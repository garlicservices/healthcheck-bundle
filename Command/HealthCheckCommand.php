<?php

namespace Garlic\HealthCheck\Command;

use Garlic\Bus\Service\CommunicatorService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;

class HealthCheckCommand extends Command
{
    protected static $defaultName = 'healthcheck:init';

    /** @var Kernel $kernel */
    protected $kernel;

    /**
     * HealthCheckCommand constructor.
     * @param string|null $name
     * @param Kernel $kernel
     */
    public function __construct(?string $name = null, Kernel $kernel)
    {
        $this->kernel = $kernel;

        parent::__construct($name);
    }

    /**
     * Description
     */
    protected function configure()
    {
        $this->setDescription('Init healthcheck on container start');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $container = $this->kernel->getContainer();

        $response = $container->get('http_kernel')
            ->handle(Request::create('/graphql', 'POST', ['query' => $this->getIntrospectionQuery()]), HttpKernelInterface::MASTER_REQUEST);

        $container->get(CommunicatorService::class)
            ->command('gateway')
            ->post()
            ->serviceRebuildSchema([], [
                'data' => $response->getContent(),
                'name' => getenv('SERVICE_NAME'),
                'timing' => 0
            ]);

        $io->success('Init healthcheck message sent.');
    }

    /**
     * Self-introspection
     * @return false|string
     */
    protected function getIntrospectionQuery()
    {
        return file_get_contents(__DIR__ . '/../Resources/query/introspection.graphql');
    }
}
