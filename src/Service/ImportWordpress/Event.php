<?php
    
    namespace Partitech\SonataExtra\Service\ImportWordpress;
    
    use Symfony\Component\EventDispatcher\EventDispatcherInterface;
    use Partitech\SonataExtra\Event\ImportWpProgressEvent;
    
    class Event
    {
        private const array STEPS = [
            'medias' => [
                'init' => 'Import medias',
                'percent_start' => 0,
            ],
            'users' => [
                'init' => 'Import users',
                'percent_start' => 20,
            ],
            'categories' => [
                'init' => 'Import categories',
                'percent_start' => 40,
            ],
            'tags' => [
                'init' => 'Import tags',
                'percent_start' => 60,
            ],
            'posts' => [
                'init' => 'Import posts',
                'percent_start' => 80,
            ],
            'pages' => [
                'init' => 'Import pages',
                'percent_start' => 90,
            ]
        ];
        public const string MEDIAS_STEP = 'medias';
        public const string USERS_STEP      = 'users';
        public const string CATEGORIES_STEP = 'categories';
        public const string TAGS_STEP  = 'tags';
        public const string POSTS_STEP = 'posts';
        public const string PAGES_STEP = 'pages';
        private int $count = 0;
        private int $iteration = 0;
        public const int STEP_PERCENT = 20;
        
        private string $currentStep = 'media';
        
        public function __construct(
            private readonly EventDispatcherInterface $eventDispatcher,
        ){}
        
        
        public function setCurrentStep(?string $currentStep)
        : Event {
            $this->currentStep = $currentStep;
            $this->init();
            return $this;
        }
        
        public function init():void
        {
            $this->iteration = 0;

            $this->sendEvent(self::STEPS[$this->currentStep]['percent_start'], self::STEPS[$this->currentStep]['init'], null);
        }
        
        public function sendEvent($progress, $message, $currentJob): void
        {
            $this->eventDispatcher->dispatch(
                new ImportWpProgressEvent($progress, $message, $currentJob),
                ImportWpProgressEvent::NAME
            );
        }
        
        public function setCount(int $count): self
        {
            $this->count = $count;
            return $this;
        }
        
        public function setJob(string $currentJob): void
        {
            $this->iteration++;
            $progress = self::STEPS[$this->currentStep]['percent_start'] + ( ( self::STEP_PERCENT / $this->count) * $this->iteration );
            $message = self::STEPS[$this->currentStep]['init'] . ' ' . $this->iteration .'/'. $this->count ;
            $this->sendEvent($progress, $message, $currentJob);
        }
        
    }