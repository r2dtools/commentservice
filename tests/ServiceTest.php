<?php

namespace CommentService\Tests;

use CommentService\Comment;
use CommentService\Exception\BadResponseException;
use CommentService\Exception\InvalidDataException;
use CommentService\Service;
use Http\Mock\Client;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Http\Message\RequestMatcher\RequestMatcher;

class ServiceTest extends TestCase
{
    /**
     * @dataProvider getCommentsDataProvider
     * @throws ClientExceptionInterface
     */
    public function testGetCommentsSuccess(int $statusCode, string $content, array $expectedComments): void
    {
        $client = new Client();
        $client->on(
            new RequestMatcher('/comments', '', ['GET']),
            function () use ($statusCode, $content) {
                return $this->getResponseMock($statusCode, $content);
            }
        );
        $service = new Service($client, '');
        $comments = $service->getComments();
        $this->assertEquals($expectedComments, $comments);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws \Exception
     */
    public function testGetCommentsFailure(): void
    {
        $client = new Client();
        $client->on(
            new RequestMatcher('/comments', '', ['GET']),
            function () {
                return $this->getResponseMock(200, '[bad_json]');
            }
        );
        $service = new Service($client, '');
        $this->expectException(InvalidDataException::class);
        $service->getComments();
    }

    /**
     * @throws ClientExceptionInterface
     * @throws \Exception
     */
    public function testCreateCommentSuccess(): void
    {
        $client = new Client();
        $client->on(
            new RequestMatcher('/comment', '', ['POST']),
            function (RequestInterface $request) {
                if ($request->getBody()->getContents() === '{"name":"name1","text":"text1"}') {
                    return $this->getResponseMock(200, '{"id": 1,"name": "name1","text": "text1"}');
                }
                return $this->getResponseMock(400, 'bad request');
            }
        );

        $expectedComment = new Comment('name1', 'text1', 1);
        $service = new Service($client, '');
        $comment = $service->createComment(new Comment('name1', 'text1'));
        $this->assertEquals($expectedComment, $comment);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws \Exception
     */
    public function testCreateCommentFailure(): void
    {
        $client = new Client();
        $client->on(
            new RequestMatcher('/comment', '', ['POST']),
            function () {
                return $this->getResponseMock(400, 'bad request');
            }
        );

        $service = new Service($client, '');
        $this->expectException(BadResponseException::class);
        $this->expectErrorMessage('bad request');
        $service->createComment(new Comment('name1', 'text1'));
    }

    /**
     * @throws ClientExceptionInterface
     * @throws \Exception
     */
    public function testUpdateCommentSuccess(): void
    {
        $client = new Client();
        $client->on(
            new RequestMatcher('/comment/1', '', ['PUT']),
            function (RequestInterface $request) {
                if ($request->getBody()->getContents() === '{"name":"name1","text":"text2","id":1}') {
                    return $this->getResponseMock(200, '{"id": 1,"name": "name1","text": "text2"}');
                }
                return $this->getResponseMock(400, 'bad request');
            }
        );

        $service = new Service($client, '');
        $comment = new Comment('name1', 'text1', 1);
        $comment->setText('text2');
        $nComment = $service->updateComment($comment);
        $this->assertEquals($comment, $nComment);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws \Exception
     */
    public function testUpdateCommentFailure(): void
    {
        $client = new Client();
        $client->on(
            new RequestMatcher('/comment/1', '', ['PUT']),
            function () {
                return $this->getResponseMock(200, '');
            }
        );

        $service = new Service($client, '');
        $this->expectException(InvalidDataException::class);
        $service->updateComment(new Comment('name1', 'text1', 1));
    }

    /**
     * @return array[]
     */
    public function getCommentsDataProvider(): array
    {
        return [
            [
                200,
                '[]',
                [],
            ],
            [
                200,
                '[{"id": 1,"name": "name1","text": "text1"}, {"id": 2,"name": "name2","text": "text2"}]',
                [new Comment('name1', 'text1', 1), new Comment('name2', 'text2', 2)],
            ],
        ];
    }

    /**
     * @param int $statusCode
     * @param string $content
     * @return ResponseInterface
     */
    private function getResponseMock(int $statusCode, string $content): ResponseInterface
    {
        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock->method('getContents')->willReturn($content);
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getBody')->willReturn($streamMock);
        $responseMock->method('getStatusCode')->willReturn($statusCode);

        return $responseMock;
    }
}
