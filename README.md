# Garlic healthcheck bundle
This bundle allows to form graphQL introspection schema as a self-describing method and send it back to gateway to process and merge it.
Target microservice subscribing on ```serviceDiscovery``` event and forming ```serviceRebuildSchema``` command with response data as
```json
{
  "name": "microservice_name",
  "data": "{...introspection_json_string}"
}
```

## Installation

Just a one thing are necessary for this bundle works. 

### Add garlic/bus bundle to your composer.json

```bash
composer require garlic/healthcheck-bundle
```

## Usage

Service automatically begin to listen enqueue events with name ```serviceDiscovery``` and generate proper ```COMMAND``` for gaeway to rebuild actual whole schema introspection with new obtained data.

Data structure could be obtained at [introspection.json](https://github.com/garlicservices/healthcheck-bundle/tree/master/Resources/query/introspection.json)

### How to init event

```bash
$container->get(CommunicatorService::class)
    ->serviceDiscoveryEvent(['date' => microtime(true)]);
```

#### Response
[Data structure](https://github.com/garlicservices/healthcheck-bundle/blob/master/Service/Processor/ServiceDiscoveryProcessor.php#L40) to work with