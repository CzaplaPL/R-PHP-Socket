<?php

declare(strict_types=1);

namespace App\Connection;

use App\Connection\Client\ConnectionSettings;
use App\Connection\Server\ServerSettings;
use App\Core\DataDTO;
use App\Core\Exception\ConnectionErrorException;
use App\Core\Exception\ConnectionFailedException;
use App\Core\Exception\CreateFrameOnUnsuportedVersionException;
use App\Core\PayloadDTO;
use App\Frame\CancelFrame;
use App\Frame\Enums\ErrorType;
use App\Frame\ErrorFrame;
use App\Frame\Factory\IFrameFactory;
use App\Frame\FireAndForgetFrame;
use App\Frame\Frame;
use App\Frame\KeepAliveFrame;
use App\Frame\LeaseFrame;
use App\Frame\PayloadFrame;
use App\Frame\ReasumeFrame;
use App\Frame\ReasumeOkFrame;
use App\Frame\RequestChannelFrame;
use App\Frame\RequestNFrame;
use App\Frame\RequestResponseFrame;
use App\Frame\RequestStreamFrame;
use App\Frame\SetupFrame;
use Ramsey\Uuid\UuidInterface;
use Ratchet\Client\WebSocket;
use React\EventLoop\Loop;
use React\EventLoop\TimerInterface;
use React\Socket\ConnectionInterface;
use Rx\Observable;
use Rx\Subject\Subject;
use Throwable;

abstract class RSocketConnection
{
    private readonly Subject $setupedSubject;
    private readonly Subject $closeSubject;
    private readonly Subject $recivedRequestSubject;
    /**
     * @var array<int,int>
     */
    private array $requestNLimit = [];

    /**
     * @var Frame[]
     */
    private array $frameToSend = [];
    protected bool $connectIsSetuped = false;
    protected int $timeOfLastKeepAliveMessage = 0;
    /**
     * @var Frame[]
     */
    protected array $recivedFrames = [];
    /**
     * @var Frame[]
     */
    protected array $sendedFrame = [];
    protected bool $reasumeEnable = false;
    protected ?LeaseMenager $leaseMenager = null;
    /**
     * @var array<int,Subject>
     */
    protected array $lisseners = [];

    protected int $nextStreamId;
    protected ?TimerInterface $sendKeepAliveTimer = null;
    protected ?TimerInterface $timeoutTimer = null;

    public function __construct(
        public string $reasumeToken,
        protected readonly ConnectionInterface|WebSocket $connection,
        protected readonly IFrameFactory $frameFactory,
        protected readonly ServerSettings $settings = new ServerSettings(),
    ) {
        $this->setupedSubject = new Subject();
        $this->closeSubject = new Subject();
        $this->recivedRequestSubject = new Subject();
        $this->nextStreamId = 1;
        $this->connection->on('data', $this->handleData(...));
        $this->connection->on('close', $this->handleClose(...));
    }

    public function close(): void
    {
        $this->connection->close();
    }

    public function fireAndForget(string $data, string $metaData = null): void
    {
        $this->send(new FireAndForgetFrame(
            $this->nextStreamId,
            $data,
            $metaData,
        ));


        $this->nextStreamId += 2;
    }

    public function onRecivedRequest(): Observable
    {
        return $this->recivedRequestSubject->asObservable();
    }

    public function requestResponse(string $data, string $metaData = null): Observable
    {
        $subject = new Subject();
        $this->lisseners[$this->nextStreamId] = $subject;

        $this->send(new RequestResponseFrame(
            $this->nextStreamId,
            $data,
            $metaData,
        ));
        $this->nextStreamId += 2;

        return $subject->asObservable();
    }

    public function requestStream(int $requestN, string $data, string $metaData = null): ChannelRequest
    {
        $subject = new Subject();
        $this->lisseners[$this->nextStreamId] = $subject;

        $this->send(new RequestStreamFrame(
            $this->nextStreamId,
            $requestN,
            $data,
            $metaData,
        ));


        $channelRequest = new ChannelRequest(
            $this->nextStreamId,
            $subject->asObservable()
        );

        $this->nextStreamId += 2;

        return $channelRequest;

    }

    public function requestChannel(int $requestN, string $data, string $metaData = null): ChannelRequest
    {
        $subject = new Subject();
        $this->lisseners[$this->nextStreamId] = $subject;

        $this->send(new RequestChannelFrame(
            $this->nextStreamId,
            $requestN,
            $data,
            $metaData,
        ));
        $this->requestNLimit[$this->nextStreamId] = 0;

        $channelRequest = new ChannelRequest(
            $this->nextStreamId,
            $subject->asObservable()
        );
        $this->nextStreamId += 2;


        return $channelRequest;
    }

    public function sendRequestN(int $streamId, int $requestN ): void
    {
        $this->send(new RequestNFrame(
            $streamId,
            $requestN,
        ));
    }

    public function cancle(int $streamId ): void
    {
        $this->lisseners[$streamId]?->onCompleted();

        $this->send(new CancelFrame(
            $streamId,
        ));
    }

    public function connect(ConnectionSettings $settings = new ConnectionSettings(), DataDTO $data = null, DataDTO $metaData = null): void
    {
        try {
            $setupFrame = SetupFrame::fromSettings($settings);

            if ($setupFrame->reasumeEnable) {
                $this->reasumeEnable = true;
            }

            if ($data) {
                $setupFrame = $setupFrame->setData($data);
            }
            if ($metaData) {
                $setupFrame = $setupFrame->setMetaData($metaData);
            }

            $this->send($setupFrame);
            $this->connectIsSetuped = true;
            $this->setupKeepAlive($setupFrame);
            $this->nextStreamId = 2;
            $this->timeOfLastKeepAliveMessage = time();
        } catch (Throwable $error) {
            throw ConnectionFailedException::errorOnSendSetupFrame($error);
        }
    }

    public function isConnectSetuped(): bool
    {
        return $this->connectIsSetuped;
    }

    public function onClose(): Observable
    {
        return $this->closeSubject->asObservable();
    }

    public function onConnect(): Observable
    {
        return $this->setupedSubject->asObservable();
    }

    public function isReasumeEnable(): bool
    {
        return $this->reasumeEnable;
    }

    public function sendResponse(int $streamId, string $data, string $metaData = null, bool $complete = false): void
    {
        $frame = new PayloadFrame(
            $streamId,
            $data,
            false,
            $complete,
            true,
            $metaData
        );

        if ($this->canSend($streamId)) {
            if (isset($this->requestNLimit[$streamId])) {
                --$this->requestNLimit[$streamId];
            }

            $this->sendedFrame[] = $frame;

            $this->send($frame);
            if(isset($this->leaseMenager)){
                $this->leaseMenager->send();
            }

            return;
        }

        $this->frameToSend[] = $frame;
    }

    public function reasume(): void {
        $recivedPosition = 0;
        foreach ($this->recivedFrames as $recivedFrame){
            $recivedPosition += strlen($recivedFrame->serialize());
        }

        $this->send(new ReasumeFrame(
            $this->reasumeToken,
            $recivedPosition,
            0
        ));
    }

    public function getData($streamId): ?Observable {
        return $this->lisseners[$streamId]?->asObservable() ?? null;
    }

    /**
     * @return iterable<Frame>
     */
    abstract protected function decodeFrames(string $data): iterable;

    abstract protected function send(Frame $frame): bool;

    abstract protected function end(Frame $frame): void;

    private function setupConnection(SetupFrame $frame): void
    {
        if ($this->connectIsSetuped) {
            $this->send(new ErrorFrame(
                Frame::SETUP_STREAM_ID,
                ErrorType::REJECTED_SETUP,
                'The connection is already setuped'
            ));

            return;
        }

        if ($frame->reasumeEnable && false === $this->settings->isReasumeEnable()) {
            $this->end(new ErrorFrame(
                Frame::SETUP_STREAM_ID,
                ErrorType::UNSUPPORTED_SETUP,
                'No resume support'
            ));

            return;
        }

        if ($this->settings->isLeaseRequire() && false === $frame->leaseEnable) {
            $this->end(new ErrorFrame(
                Frame::SETUP_STREAM_ID,
                ErrorType::REJECTED_SETUP,
                'Server need lease'
            ));

            return;
        }

        if (
            $frame->leaseEnable
            && false === $this->settings->isLeaseEnable()
            && false === $this->settings->isLeaseRequire()
        ) {
            $this->end(new ErrorFrame(
                Frame::SETUP_STREAM_ID,
                ErrorType::UNSUPPORTED_SETUP,
                'No lease support'
            ));

            return;
        }

        if($frame->leaseEnable){
            $this->leaseMenager = new LeaseMenager();
        }

        $this->reasumeEnable = $frame->reasumeEnable;
        $this->reasumeToken = $frame->reasumeToken;

        $this->connectIsSetuped = true;
        $this->setupKeepAlive($frame);
        $this->setupedSubject->onNext($frame);
    }

    private function handleClose(ErrorFrame $errorFrame = null): void
    {
        $this->connectIsSetuped = false;
        if (isset($this->timeoutTimer)) {
            Loop::get()->cancelTimer($this->timeoutTimer);
        }

        if (isset($this->sendKeepAliveTimer)) {
            Loop::get()->cancelTimer($this->sendKeepAliveTimer);
        }

        $this->closeSubject->onNext(
            new ClosedConnection(
                $this,
                $errorFrame ? new ConnectionErrorException($errorFrame->errorMesage(), $errorFrame) : null
            )
        );

        if (false === $this->reasumeEnable) {
            $this->closeSubject->onCompleted();
        }
    }

    private function reasumeConnection(ReasumeFrame $frame):void
    {
        $recivedPosition = 0;
        foreach ($this->recivedFrames as $recivedFrame){
            $recivedPosition += strlen($recivedFrame->serialize());
        }

        if(  $frame->availablePosition > $recivedPosition) {
            $this->send(new ErrorFrame(
                1,
                ErrorType::REJECTED_RESUME
            ));
        }

        $this->send(new ReasumeOkFrame(
            $recivedPosition
        ));

        $this->connectIsSetuped = true;

    }

    private function handleData(string $data): void
    {
        try {
            foreach ($this->decodeFrames($data) as $frame) {
                if ($frame instanceof SetupFrame) {
                    $this->setupConnection($frame);
                    continue;
                }

                if ($frame instanceof ErrorFrame) {
                    $this->handleError($frame);
                    continue;
                }

                if ($frame instanceof KeepAliveFrame) {
                    $this->handleKeepAlive($frame);
                    continue;
                }

                if ($frame instanceof RequestNFrame) {
                    $this->handleRequestN($frame);
                    continue;
                }

                if ($frame instanceof LeaseFrame) {
                    if(isset($this->leaseMenager)){
                        $this->leaseMenager->setNewLimit($frame);
                    }
                    continue;
                }

                if ($frame instanceof ReasumeFrame) {
                    $this->reasumeConnection($frame);
                    continue;
                }

                if (
                    $frame instanceof FireAndForgetFrame
                    || $frame instanceof RequestResponseFrame
                    || $frame instanceof RequestStreamFrame
                    || $frame instanceof RequestChannelFrame
                ) {
                    $this->recivedFrames[] = $frame;

                    if ($frame instanceof RequestStreamFrame) {
                        $this->requestNLimit[$frame->streamId()] = $frame->getRequestN();
                    }

                    if ($frame instanceof RequestChannelFrame) {
                        $this->requestNLimit[$frame->streamId()] = $frame->getRequestN();
                        $subject = new Subject();
                        $this->lisseners[$frame->streamId()] = $subject;
                    }
                    $this->recivedRequestSubject->onNext($frame);
                }

                if ($frame instanceof PayloadFrame) {
                    $subject = $this->lisseners[$frame->streamId()] ?? null;
                    if ($subject) {
                        if ($frame->next()) {
                            $subject->onNext(new PayloadDTO($frame->streamId(), $frame->getData(), $frame->getMetaData()));
                        }
                        if ($frame->complete()) {
                            if (isset($this->requestNLimit[$frame->streamId()])) {
                                unset($this->requestNLimit[$frame->streamId()]);
                            }
                            $subject->onCompleted();
                        }
                    }
                }
            }
        } catch (CreateFrameOnUnsuportedVersionException $e) {
            $this->end(new ErrorFrame(
                Frame::SETUP_STREAM_ID,
                ErrorType::INVALID_SETUP,
                'Version not supported'
            ));
        }
    }

    private function canSend($streamId): bool
    {
        if (isset($this->requestNLimit[$streamId])) {
            return $this->requestNLimit[$streamId] > 0;
        }

        if(isset($this->leaseMenager)){
            return $this->leaseMenager->canSend();
        }

        return true;
    }

    private function handleError(ErrorFrame $frame): void
    {
        if (0 === $frame->streamId()) {
            foreach ($this->lisseners as $lissener) {
                $lissener->onError(new ConnectionErrorException($frame->errorMesage(), $frame));
                $this->handleClose($frame);
            }

            return;
        }

        if (isset($this->lisseners[$frame->streamId()])) {
            $this->lisseners[$frame->streamId()]->onError(new ConnectionErrorException($frame->errorMesage(), $frame));

            return;
        }

        throw new ConnectionErrorException($frame->errorMesage(), $frame);
    }

    private function handleKeepAlive(KeepAliveFrame $frame): void
    {
        $this->timeOfLastKeepAliveMessage = time();
        if ($frame->needResponse()) {
            $this->send(new KeepAliveFrame(false));
        }
    }

    private function setupKeepAlive(SetupFrame $frame): void
    {
        $this->timeOfLastKeepAliveMessage = time();
        $this->sendKeepAliveTimer = Loop::get()->addPeriodicTimer($frame->keepAlive / 1000, function (): void {
            $this->send(new KeepAliveFrame(true));
        });

        $this->timeoutTimer = Loop::get()->addPeriodicTimer($frame->lifetime / 1000, function () use ($frame): void {
            if (time() - $this->timeOfLastKeepAliveMessage > $frame->lifetime) {
                $this->connection->close();
            }
        });
    }

    private function handleRequestN(RequestNFrame $frame): void
    {
        if (!isset($this->requestNLimit[$frame->streamId()])) {
            $this->requestNLimit[$frame->streamId()] = 0;
        }

        $this->requestNLimit[$frame->streamId()] += $frame->requestN;

        if (!$this->canSend($frame->streamId())) {
            return;
        }

        $keyToUnsset = [];
        foreach ($this->frameToSend as $key => $frameToSend) {
            if ($frameToSend->streamId() !== $frame->streamId()) {
                continue;
            }

            if (!$this->canSend($frame->streamId())) {
                break;
            }

            if(isset($this->leaseMenager)){
                $this->leaseMenager->send();
            }

            $this->send($frameToSend);
            $keyToUnsset[] = $key;
        }

        foreach ($keyToUnsset as $key) {
            unset($this->frameToSend[$key]);
        }
    }


}
