# Garlic healthcheck bundle
This bundle allows to form graphQL introspection schema as a self-describing method and send it back to gateway to process and merge it.
Target microservice subscribing on ```serviceDiscovery``` event and forming ```serviceRebuildSchema``` command with response data as
```json
[
  'name' => 'microservice_name',
  'data' => 'introspection_json_string',
]
```

## Installation

Just a one thing are necessary for this bundle works. 

### Add garlic/bus bundle to your composer.json

```bash
composer require garlic/healthcheck-bundle
```