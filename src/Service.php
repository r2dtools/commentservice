<?php

namespace CommentService;

use CommentService\Exception\BadResponseException;
use CommentService\Exception\InvalidDataException;
use CommentService\Utils\Json;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\ClientExceptionInterface;

class Service
{
    /**
     * @var ClientInterface
     */
    private ClientInterface $httpClient;

    /**
     * @var string
     */
    private string $baseUri;

    /**
     * @param ClientInterface $httpClient
     * @param string $baseUri
     */
    public function __construct(ClientInterface $httpClient, string $baseUri)
    {
        $this->httpClient = $httpClient;
        $this->baseUri = trim($baseUri, '/');
    }

    /**
     * @return Comment[]
     * @throws ClientExceptionInterface
     * @throws \Exception
     */
    public function getComments(): array
    {
        $comments = [];
        $content = $this->sendRequest('GET', '/comments');

        foreach ($content as $item) {
            $comments[] = new Comment($item['name'], $item['text'], $item['id']);
        }

        return $comments;
    }

    /**
     * @param Comment $comment
     * @return Comment
     * @throws BadResponseException
     * @throws ClientExceptionInterface
     * @throws InvalidDataException
     */
    public function createComment(Comment $comment): Comment
    {
        $body = $this->encodeBodyContent($comment->toArray());
        $content = $this->sendRequest('POST', '/comment', $body);

        return new Comment($content['name'], $content['text'], $content['id']);
    }

    /**
     * @param Comment $comment
     * @return Comment
     * @throws BadResponseException
     * @throws ClientExceptionInterface
     * @throws InvalidDataException
     */
    public function updateComment(Comment $comment): Comment
    {
        $body = $this->encodeBodyContent($comment->toArray());
        $content = $this->sendRequest('PUT', "/comment/{$comment->getId()}", $body);

        return new Comment($content['name'], $content['text'], $content['id']);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param string|null $body
     * @return array
     * @throws BadResponseException
     * @throws ClientExceptionInterface
     * @throws InvalidDataException
     */
    private function sendRequest(string $method, string $uri, string $body = null): array
    {
        $request = new Request($method, "{$this->baseUri}{$uri}", ['Content-Type' => 'application/json'], $body);
        $response = $this->httpClient->sendRequest($request);
        $content = $response->getBody()->getContents();
        if ($response->getStatusCode() !== 200) {
            throw new BadResponseException($content, $response->getStatusCode());
        }

        return $this->decodeBodyContent($content);
    }

    /**
     * @param string $content
     * @return array
     * @throws InvalidDataException
     */
    private function decodeBodyContent(string $content): array
    {
        if (empty($content)) {
            throw new InvalidDataException("Response data is empty");
        }

        try {
            return Json::decode($content);
        } catch (\Exception $e) {
            throw new InvalidDataException("Invalid response data: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    /**
     * @param array $data
     * @return string
     * @throws InvalidDataException
     */
    private function encodeBodyContent(array $data): string
    {
        try {
            return Json::encode($data);
        } catch (\Exception $e) {
            throw new InvalidDataException("Invalid request data: {$e->getMessage()}", $e->getCode(), $e);
        }
    }
}
