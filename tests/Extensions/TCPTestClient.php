<?php

declare(strict_types=1);

namespace App\Tests\Extensions;

use App\Connection\Client\ConnectionSettings;
use App\Connection\Client\IRSocketClient;
use App\Connection\Server\IRSocketServer;
use App\Connection\WSRSocketConnection;
use App\Core\ArrayBuffer;
use App\Core\DataDTO;
use App\Core\Exception\ConnectionFailedException;
use App\Frame\Factory\FrameFactory;
use App\Frame\SetupFrame;
use App\Tests\Extensions\Constraint\ExpectedSendFrame;
use PHPUnit\Framework\Constraint\Constraint;
use React\EventLoop\Loop;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;
use React\Socket\SocketServer;
use Rx\Observable;
use Rx\Subject\Subject;
use Throwable;

/**
 * @internal
 */
final class TCPTestClient
{
    private ?ConnectionInterface $connection = null;
    private Observable $recivedMessage;
    private int $sizeOfNextFrame = 0;
    private string $previusData = '';


    public function __construct(private FrameFactory $frameFactory)
    {
        $this->recivedMessage = new Subject();
    }


    public function connect(string $url): PromiseInterface
    {
        return new Promise(function (callable $resolver, callable $reject) use ($url): void {
            $conector =new Connector();
            $conector->connect($url)
                ->then(
                    onFulfilled: function (ConnectionInterface $connection) use ($resolver, $reject): void {
                        try {
                            $this->connection = $connection;
                            $this->connection->on('data', function (string $data){
                                foreach ($this->decodeFrames($data) as $frame){
                                    $this->recivedMessage->onNext($frame);
                                }


                            });
                            $resolver($connection);
                        } catch (Throwable $error) {
                            $reject($error);
                        }
                    },
                    onRejected: function (Throwable $error) use ($reject): void {
                        $reject($error);
                    }
                );
        });
    }

    public function SendSetupFrame(
        ConnectionSettings $settings = new ConnectionSettings(),
        DataDTO $data = null,
        DataDTO $metaData = null
    ): void{
        if($this->connection === null) {
            throw new \Exception("Client Not Connected");
        }
        $setupFrame = SetupFrame::fromSettings($settings);

        if ($data) {
            $setupFrame = $setupFrame->setData($data);
        }
        if ($metaData) {
            $setupFrame = $setupFrame->setMetaData($metaData);
        }

        $this->write($setupFrame->serialize());
    }

    public function write(string $data): void
    {
        $sizeBuffer = new ArrayBuffer();
        $sizeBuffer->addUInt24(strlen($data));
        if(is_null($this->connection)){
            throw new \Exception("connect To server before send data");
        }
        $this->connection->write($sizeBuffer->toString().$data);
    }

    public function close()
    {
        $this->connection?->close();
    }

    public function recivedMessage(): Observable
    {
        return $this->recivedMessage->asObservable();
    }

    private function decodeFrames(string $data): iterable
    {
        $data = $this->previusData.$data;

        if (0 === $this->sizeOfNextFrame) {
            $this->sizeOfNextFrame = $this->getFrameSize($data);
            $data = substr($data, 3);
        }

        while ($this->sizeOfNextFrame > 0 && strlen($data) >= $this->sizeOfNextFrame) {
            $frameString = substr($data, 0, $this->sizeOfNextFrame);

            yield $this->frameFactory->create($frameString);

            $data = substr($data, $this->sizeOfNextFrame);
            $this->sizeOfNextFrame = $this->getFrameSize($data);
            $data = substr($data, 3);
        }

        $this->previusData = $data;
    }

    private function getFrameSize(string $data): int
    {
        if (strlen($data) < 3) {
            return 0;
        }

        $sizeString = substr($data, 0, 3);
        $sizeBuffer = new ArrayBuffer([
            ord($sizeString[0]),
            ord($sizeString[1]),
            ord($sizeString[2]),
        ]);

        return $sizeBuffer->getUInt24(0);
    }
}
