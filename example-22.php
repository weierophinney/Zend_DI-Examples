<?php

namespace Events {

    interface SharedEventCollectionInterface
    {
    }

    interface SharedEventCollectionAware
    {
        public function setSharedEventCollection(SharedEventCollectionInterface $sharedEvents);
    }

    interface EventCollectionInterface extends SharedEventCollectionAware
    {
    }

    interface EventCollectionAware
    {
        public function setEventCollection(EventCollectionInterface $events);
    }

    class SharedEventManager implements SharedEventCollectionInterface
    {
    }

    class EventManager implements EventCollectionInterface
    {
        public $counter = 0;
        public $sharedEvents;
        public function setSharedEventCollection(SharedEventCollectionInterface $sharedEvents)
        {
            $this->counter++;
            $this->sharedEvents = $sharedEvents;
        }
    }

    class Collection implements EventCollectionAware {
        public $counter = 0;
        public $collection;
        public function setEventCollection(EventCollectionInterface $events)
        {
            $this->counter++;
            $this->collection = $events;
        }
    }

    class Collection2 extends Collection {
    }
}

namespace {
    // bootstrap
    include 'zf2bootstrap' . ((stream_resolve_include_path('zf2bootstrap.php')) ? '.php' : '.dist.php');

    $di = new Zend\Di\Di;
    $di->configure(new Zend\Di\Configuration(array(
        'instance' => array(
            'preferences' => array(
                'Events\SharedEventCollectionInterface' => 'Events\SharedEventManager',
                'Events\EventCollectionInterface'       => 'Events\EventManager',
            ),
            'Events\EventCollectionInterface' => array(
                'shared' => false,
            ),
            'Events\EventManager' => array(
                'shared' => false,
            ),
        ),
    )));
    $collection  = $di->get('Events\Collection');
    $collection2 = $di->get('Events\Collection2');

    // expression to test
    $works = ($collection->collection instanceof Events\EventManager
        && $collection->counter == 1
        && $collection->collection->sharedEvents instanceof Events\SharedEventManager
        && $collection2->collection instanceof Events\EventManager
        && $collection2->counter == 1
        && $collection2->collection->sharedEvents instanceof Events\SharedEventManager
        && $collection->collection->sharedEvents === $collection2->collection->sharedEvents
        && $collection->collection !== $collection2->collection
    );

    // display result
    echo (($works) ? 'It works!' : 'It DOES NOT work!') . PHP_EOL;
}
