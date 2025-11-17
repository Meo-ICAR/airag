<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\PdfToText\Pdf;
use Illuminate\Support\Facades\Http;
use App\Neuron\MyChatBot;
use NeuronAI\Chat\Messages\UserMessage;

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
    }
}
