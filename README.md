# Garlic healthcheck bundle
This bundle is a backend for serviceDiscoveryEvent, which is uasually a part of [garlic/gateway](https://github.com/garlicservices/gateway-bundle) bundle.

This bundle allows to form graphQL introspection schema as a self-describing method and send it back to gateway to process and merge it.

Target microservice subscribing on ```serviceDiscovery``` event and forming ```serviceRebuildSchema``` command with response data as
```json
{
  "name": "microservice_name",
  "data": "{...introspection_json_string}",
  "timing": "0.0021"
}
```

## Installation

Just a one thing are necessary for this bundle works. 

#### Add garlic/bus bundle to your composer.json

```bash
composer require garlic/healthcheck
```

#### bundles.php
config/bundles.php - add bundle initialisation
```bash
Garlic\HealthCheck\HealthCheckBundle::class => ['all' => true],
```

#### redis config
Add to your .env file next configuration values
```bash
REDIS_HOST=localhost
REDIS_PORT=6379
HEALTHCHECK_LOCK_TTL=30 - not nessessary, time in seconds until next run would be possible. Default is 30 sec
```

## Usage

Service automatically begin to listen enqueue events with name ```serviceDiscovery``` and generate proper ```COMMAND``` for gaeway to rebuild actual whole schema introspection with new obtained data.

Data structure could be obtained at [introspection.json](https://github.com/garlicservices/healthcheck-bundle/tree/master/Resources/query/introspection.json)

### How to init event from outside

```bash
$container->get(CommunicatorService::class)
    ->serviceDiscoveryEvent(['date' => microtime(true)]);
```

### How to send self-introspection manually
```bash
sf healthcheck:init
```

#### Response
[Data structure](https://github.com/garlicservices/healthcheck-bundle/blob/master/Service/Processor/ServiceDiscoveryProcessor.php#L39) to work with