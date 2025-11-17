<?php

declare(strict_types=1);

namespace App\NeuronAI;

use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\RAG\Embeddings\EmbeddingsProviderInterface;
use NeuronAI\RAG\RAG;
use NeuronAI\RAG\VectorStore\FileVectorStore;
use NeuronAI\RAG\VectorStore\VectorStoreInterface;
use NeuronAI\Agent;
use NeuronAI\SystemPrompt;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Providers\Gemini\Gemini;
use NeuronAI\Providers\HttpClientOptions;
use NeuronAI\Chat\History\ChatHistoryInterface;


class MyChatBot extends RAG
{
    protected ?string $threadId = null;
    protected function provider(): AIProviderInterface
    {
         return new Gemini(
            key: env('GEMINI_API_KEY'),
            model: env('GEMINI_MODEL', 'gemini-2.5-flash'),
            parameters: [], // Add custom params (temperature, logprobs, etc)
            httpOptions: new HttpClientOptions(timeout: 30),
        );
    }

    public function setThreadId(string $threadId): void
    {
        $this->threadId = $threadId;
    }

    protected function chatHistory(): ChatHistoryInterface
    {
        if ($this->threadId === null) {
            throw new \RuntimeException('Thread ID must be set before initializing chat history');
        }

        

        return new SQLChatHistory(
            thread_id: $this->threadId,
            pdo: $pdo,
            table: 'chat_history',
            contextWindow: 50000
        );
    }

    protected function embeddings(): EmbeddingsProviderInterface
    {
          return new GeminiEmbeddingsProvider(
            key: env('GEMINI_API_KEY'),
            model: env('GEMINI_MODEL', 'gemini-2.5-flash')
        );
        /*return new OpenAIEmbeddingsProvider(
            key: 'OPENAI_API_KEY',
            model: 'OPENAI_MODEL'
        );*/
    }
    
    protected function vectorStore(): VectorStoreInterface
    {
        return new FileVectorStore(
            directory: __DIR__,
            name: 'demo'
        );
    }
}