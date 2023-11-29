<?php

declare(strict_types=1);

namespace App\Connection;

use App\Connection\Client\ConnectionSettings;
use App\Connection\Server\ServerSettings;
use App\Core\DataDTO;
use App\Core\Exception\ConnectionFailedException;
use App\Core\Exception\CreateFrameOnUnsuportedVersionException;
use App\Frame\Enums\ErrorType;
use App\Frame\ErrorFrame;
use App\Frame\Factory\IFrameFactory;
use App\Frame\Frame;
use App\Frame\RequestResponseFrame;
use App\Frame\SetupFrame;
use Ramsey\Uuid\UuidInterface;
use Ratchet\Client\WebSocket;
use React\Socket\ConnectionInterface;
use Rx\Observable;
use Rx\Subject\Subject;
use Throwable;

abstract class RSocketConnection
{
    private readonly Subject $setupedSubject;
    private readonly Subject $closeSubject;
    protected bool $connectIsSetuped = false;
    protected bool $reasumeEnable = false;
    // todo VO
    protected int $nextStreamId;

    public function __construct(
        public readonly UuidInterface $id,
        protected readonly ConnectionInterface|WebSocket $connection,
        protected readonly IFrameFactory $frameFactory,
        protected readonly ServerSettings $settings = new ServerSettings(),
    ) {
        $this->setupedSubject = new Subject();
        $this->closeSubject = new Subject();
        $this->nextStreamId = 1;
        $this->connection->on('data', $this->handleData(...));
        $this->connection->on('close', $this->handleClose(...));
    }

    public function close(): void
    {
        $this->connection->close();
    }

    /**
     * @return iterable<Frame>
     */
    abstract protected function decodeFrames(string $data): iterable;

    abstract protected function send(Frame $frame): bool;

    abstract protected function end(Frame $frame): void;

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

    private function setupConnection(SetupFrame $frame): void
    {
        if ($this->connectIsSetuped) {
            $this->send(new ErrorFrame(
                SetupFrame::SETUP_STREAM_ID,
                ErrorType::REJECTED_SETUP,
                'The connection is already setuped'
            ));

            return;
        }

        if ($frame->reasumeEnable && false === $this->settings->isReasumeEnable()) {
            $this->end(new ErrorFrame(
                SetupFrame::SETUP_STREAM_ID,
                ErrorType::UNSUPPORTED_SETUP,
                'No resume support'
            ));

            return;
        }

        if ($this->settings->isLeaseRequire() && false === $frame->leaseEnable) {
            $this->end(new ErrorFrame(
                SetupFrame::SETUP_STREAM_ID,
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
                SetupFrame::SETUP_STREAM_ID,
                ErrorType::UNSUPPORTED_SETUP,
                'No lease support'
            ));

            return;
        }

        $this->reasumeEnable = $frame->reasumeEnable;

        $this->connectIsSetuped = true;

        $this->setupedSubject->onNext($frame);
    }

    private function handleClose(): void
    {
        $this->connectIsSetuped = false;

        $this->closeSubject->onNext(new ClosedConnection($this));

        if (false === $this->reasumeEnable) {
            $this->closeSubject->onCompleted();
        }
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

                //
                //            if($this->connectIsSetuped === false){
                //                //todo
                //            }

                /** @var Subject|null $subject */
                $subject = $this->lisseners[$frame->streamId()] ?? null;
                //                if ($subject) {
                //                    if ($frame->next()) {
                //                        $subject->onNext($frame->payload());
                //                    }
                //                    if ($frame->complete()) {
                //                        $subject->onCompleted();
                //                    }
                //                }
            }
        } catch (CreateFrameOnUnsuportedVersionException) {
            $this->end(new ErrorFrame(
                SetupFrame::SETUP_STREAM_ID,
                ErrorType::INVALID_SETUP,
                'Version not supported'
            ));
        }
    }

    private function handleError(ErrorFrame $frame): void
    {
        if (0 === $frame->streamId()) {
        }
    }

    //    public function requestResponse(string $data): Observable
    //    {
    //        $frame = new RequestResponseFrame($this->streamId, $data);
    //        $subject = new Subject();
    //        $this->lisseners[$this->streamId] = $subject;
    //        $this->send($frame);
    //        $this->streamId += 2;
    //
    //        return $subject->asObservable();
    //    }
    //
    //    public function fireAndForget(string $data): void
    //    {
    //        if ($this->connection instanceof WebSocket) {
    //            $this->connection->send($data);
    //        }
    //
    //        $this->connection->write($data);
    // //        $frame = new RequestResponseFrame($this->streamId, $data);
    // //        $this->send($frame);
    //    }
}
