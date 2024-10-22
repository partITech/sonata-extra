<?php
    
    namespace Partitech\SonataExtra\Event;
    
    use Symfony\Contracts\EventDispatcher\Event;
    
    class ImportWpProgressEvent extends Event
    {
        public const string NAME = 'import.wp.progress';
        
        
        public function __construct(
            private readonly ?int $progress,
            private readonly ?string $message = '',
            private readonly ?string $currentJob = ''
        )
        {}
        
        public function getProgress(): ?int
        {
            return $this->progress;
        }
        
        public function getMessage(): ?string
        {
            return $this->message;
        }
        
        public function getCurrentJob(): ?string
        {
            return $this->currentJob;
        }
    }