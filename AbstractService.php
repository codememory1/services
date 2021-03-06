<?php

namespace Codememory\Components\Services;

use Codememory\Components\Database\Orm\Interfaces\EntityManagerInterface;
use Codememory\Components\Database\Orm\Repository\AbstractEntityRepository;
use Codememory\Components\Database\Pack\DatabasePack;
use Codememory\Components\Event\Dispatcher;
use Codememory\Components\Event\EventDispatcher;
use Codememory\Components\Event\Exceptions\EventExistException;
use Codememory\Components\Event\Exceptions\EventNotExistException;
use Codememory\Components\Event\Exceptions\EventNotImplementInterfaceException;
use Codememory\Components\Event\Interfaces\EventDataInterface;
use Codememory\Components\Event\Interfaces\EventDispatcherInterface;
use Codememory\Components\IndividualTasks\AbstractJob;
use Codememory\Components\Profiling\Exceptions\BuilderNotCurrentSectionException;
use Codememory\Components\Profiling\ReportCreators\EventsReportCreator;
use Codememory\Components\Profiling\Resource;
use Codememory\Components\Profiling\Sections\Builders\EventsBuilder;
use Codememory\Components\Profiling\Sections\EventsSection;
use Codememory\Components\Services\Interfaces\ServiceInterface;
use Codememory\Container\ServiceProvider\Interfaces\ServiceProviderInterface;
use ReflectionException;
use Spatie\Backtrace\Backtrace;
use Spatie\Backtrace\Frame;

/**
 * Class AbstractService
 *
 * @package Codememory\Components\Service
 *
 * @author  Codememory
 */
abstract class AbstractService
{

    /**
     * @var ServiceProviderInterface
     */
    private ServiceProviderInterface $serviceProvider;

    /**
     * @var ServiceInterface
     */
    private ServiceInterface $service;

    /**
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $eventDispatcher;

    /**
     * @var Dispatcher
     */
    private Dispatcher $dispatcher;

    /**
     * @var DatabasePack
     */
    private DatabasePack $databasePack;

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @param ServiceProviderInterface $serviceProvider
     * @param DatabasePack             $databasePack
     */
    public function __construct(ServiceProviderInterface $serviceProvider, DatabasePack $databasePack)
    {

        $this->serviceProvider = $serviceProvider;

        $this->service = new Service();
        $this->eventDispatcher = new EventDispatcher();
        $this->dispatcher = new Dispatcher();
        $this->databasePack = $databasePack;
        $this->em = $this->databasePack->getEntityManager();

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Get a provider by name
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param string $name
     *
     * @return object
     */
    protected function get(string $name): object
    {

        return $this->serviceProvider->get($name);

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Execute event and listeners of the current event
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param string $eventNamespace
     * @param array  $parameters
     *
     * @throws BuilderNotCurrentSectionException
     * @throws EventExistException
     * @throws EventNotExistException
     * @throws EventNotImplementInterfaceException
     * @throws ReflectionException
     */
    protected function dispatchEvent(string $eventNamespace, array $parameters = []): void
    {

        $microTime = microtime(true);
        $this->eventDispatcher->addEvent($eventNamespace)->setParameters($parameters);

        $event = $this->eventDispatcher->getEvent($eventNamespace);

        $this->dispatcher->dispatch($event);

        $this->eventProfiling($event, $microTime);

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Send a task to the queue
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param string $job
     * @param array  $parameters
     *
     * @return void
     */
    protected function dispatchJob(string $job, array $parameters = []): void
    {

        /** @var AbstractJob $jobObject */
        $jobObject = new $job($this->databasePack, $this->serviceProvider);

        $jobObject->dispatch($parameters);

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Get a service inside another service
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param string $name
     *
     * @return AbstractService
     * @throws Exceptions\ServiceNotExistException
     * @throws ReflectionException
     * @throws Exceptions\ServiceNotExistException
     */
    protected function getService(string $name): AbstractService
    {

        $serviceReflector = $this->service->getServiceReflector($name);

        /** @var AbstractService $service */
        $service = $serviceReflector->newInstanceArgs([
            $this->serviceProvider,
            $this->databasePack
        ]);

        return $service;

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Returns Entity manager
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @return EntityManagerInterface
     */
    protected function getEntityManager(): EntityManagerInterface
    {

        return $this->em;

    }

    /**
     * =>=>=>=>=>=>=>=>=>=>=>=>=>=>
     * Get an entity repository
     * <=<=<=<=<=<=<=<=<=<=<=<=<=<=
     *
     * @param string $entity
     *
     * @return AbstractEntityRepository
     */
    protected function getRepository(string $entity): AbstractEntityRepository
    {

        return $this->getEntityManager()->getRepository($entity);

    }

    /**
     * @param EventDataInterface $event
     * @param float              $microTime
     *
     * @return void
     * @throws BuilderNotCurrentSectionException
     */
    private function eventProfiling(EventDataInterface $event, float $microTime): void
    {

        $eventsReportCreator = new EventsReportCreator(null, new EventsSection(new Resource()));
        $eventsBuilder = new EventsBuilder();

        /** @var Frame $demanded */
        $demanded = Backtrace::create()
            ->startingFromFrame(function (Frame $frame) {
                return $frame->class === static::class;
            })
            ->frames()[0];

        $eventsBuilder
            ->setEvent($event->getNamespace())
            ->setListeners(array_map(function (object|string $listener) {
                return is_callable($listener) ? 'callback' : $listener::class;
            }, $event->getListeners()))
            ->setDemanded($demanded->class, $demanded->method)
            ->setLeadTime(round((microtime(true) - $microTime) * 1000));

        $eventsReportCreator->create($eventsBuilder);

    }

}