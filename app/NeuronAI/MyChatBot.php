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
use NeuronAI\RAG\Embeddings\GeminiEmbeddingsProvider;
//use NeuronAI\Providers\OpenAI\OpenAI;
//use NeuronAI\RAG\EmbeddingProvider\OpenAIEmbeddingProvider;
use NeuronAI\Providers\HttpClientOptions;
use Illuminate\Support\Facades\DB;
use NeuronAI\Chat\History\ChatHistoryInterface;
use NeuronAI\Chat\History\SQLChatHistory;


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
       
        /*
        return new OpenAI(
            key: env('OPENAI_API_KEY'),
            model: env('OPENAI_MODEL')
        );
        */
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

        // Get PDO instance from the default database connection
        $pdo = DB::connection()->getPdo();

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
            model: env('GEMINI_EMBEDDINGS_MODEL', 'gemini-2.5-flash')
        );
       
        /*
        return new OpenAIEmbeddingsProvider(
            key: env('OPENAI_API_KEY'),
            model: env('OPENAI_MODEL')
        );
        */
    }
    
    protected function vectorStore(): VectorStoreInterface
    {
        // Use Laravel's storage path for better file management
        $storagePath = storage_path('app/vector_stores');
        
        // Create directory if it doesn't exist
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0755, true);
        }
        
        return new FileVectorStore(
            directory: $storagePath,
            name: 'demo'
        );
    }

     public function instructions(): string
    {
        return (string) new SystemPrompt(
            background: ["You are an AI Agent specialized financial analysis"],
        );
    }
}