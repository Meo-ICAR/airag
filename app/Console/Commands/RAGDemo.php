<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\PdfToText\Pdf;
use Illuminate\Support\Facades\Http;
use App\NeuronAI\MyChatBot;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\RAG\DataLoader\FileDataLoader;


class RAGDemo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rag:demo {pdf : Path to the PDF file to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Demonstrate RAG (Retrieval-Augmented Generation) with PDF documents';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧠 NeuronAI Laravel DB Demo');
        $this->info('========================');

        if (!env('GOOGLE_API_KEY')) {
            $this->error('❌ GOOGLE_API_KEY not set in .env file');
            $this->info('Please add your Google API configuration to the .env file:');
            $this->line('GOOGLE_API_KEY=your-google-api-key');
            $this->line('GEMINI_MODEL=gemini-2.5-flash');
            return 1;
        }

        $agent = new MyChatBot();
        
        // For console commands, use a deterministic thread ID based on current date
        $threadId = 'demo-' . now()->format('Ymd-His');
        $agent->setThreadId($threadId);
        $this->info("Using thread ID: " . $threadId);
        /*
        $agent->addDocuments(
            // Use the file data loader component to process a text file
            FileDataLoader::for(__DIR__.'/my-article.md')->getDocuments()
        );
        */
        $documents = FileDataLoader::for('public')
    ->addReader('pdf', new \NeuronAI\RAG\DataLoader\PdfReader())
    ->getDocuments();
        $agent::make()->addDocuments($documents);    
        return $this->interactiveMode($agent);
        /*
        $pdfPath = $this->argument('pdf');

        // Validate the PDF file exists
        if (!file_exists($pdfPath)) {
            $this->error("The PDF file does not exist at: {$pdfPath}");
            return 1;
        }

        $this->info("Extracting text from PDF: " . basename($pdfPath));
        
        try {
            // Extract text from PDF
            $text = (new Pdf())
                ->setPdf($pdfPath)
                ->text();

            if (empty(trim($text))) {
                $this->warn('The PDF appears to be empty or text could not be extracted.');
                return 0;
            }

            // For demonstration, we'll just show a preview and some statistics
            $this->info("\n=== PDF Text Preview ===");
            $preview = substr($text, 0, 500) . (strlen($text) > 500 ? '...' : '');
            $this->line(wordwrap($preview, 120));
            
            $this->info("\n=== Document Statistics ===");
            $this->line("Total characters: " . strlen($text));
            $this->line("Total words: " . str_word_count($text));
            $this->line("Total pages: " . count(explode("\f", $text)));

            // In a real RAG system, you would:
            // 1. Split the text into chunks
            // 2. Generate embeddings for each chunk
            // 3. Store them in a vector database
            // 4. Implement retrieval logic
            
            $this->info("\nRAG Demo completed. In a full implementation, we would:");
            $this->line("- Split the document into chunks");
            $this->line("- Generate embeddings for semantic search");
            $this->line("- Store in a vector database");
            $this->line("- Implement retrieval and generation");
      
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error processing PDF: " . $e->getMessage());
            return 1;
        }
        */
       
    }

    protected function interactiveMode(MyChatBot $agent): int
    {
        $this->info("🤖 Interactive Chat with RAG Agent");
        $this->info("Type \"quit\" to exit");

        while (true) {
            $message= $this->ask("\nYou:");

            if (strtolower($message) === 'quit') {
                break;
            }

            $this->info("\n🤖 Agent is thinking...");

            $maxRetries = 3;
            $retryCount = 0;
            $success = false;

            while ($retryCount < $maxRetries && !$success) {
                try {
                    $response = $agent->chat(new UserMessage($message));
                    $this->line("Agent: " . $response->getContent());
                    $this->newLine();
                    $success = true;
                } catch (\Exception $e) {
                    $errorMessage = $e->getMessage();
                    $isServiceUnavailable = str_contains($errorMessage, '503') ||
                                         str_contains(strtolower($errorMessage), 'service unavailable');

                    if ($isServiceUnavailable && $retryCount < $maxRetries - 1) {
                        $retryCount++;
                        $this->warn("⚠️  Service temporarily unavailable. Retrying ({$retryCount}/{$maxRetries})...");
                        sleep(2 * $retryCount); // Exponential backoff
                        continue;
                    }

                    $this->error("Error: " . ($isServiceUnavailable ?
                        'The AI service is currently overloaded. Please try again later.' :
                        $errorMessage));

                    if (!$isServiceUnavailable) {
                        return 1;
                    }
                    break;
                }
            }
        }
        return 0;
    }
}
