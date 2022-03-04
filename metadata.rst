Metadata
========

The process of attributing your entities results in an array of metadata that
is used internal to this library.  If you have a very large number of
attributed entities it may be faster to cache your metadata instead of
rebuilding it with each request.

.. code-block:: php
  :linenos:

  <?php

  use ApiSkeletons\Doctrine\GraphQL\Driver;
  use ApiSkeletons\Doctrine\GraphQL\Metadata;

  $metadataConfig = $cache->get('GraphQL');

  if (! $metadataConfig) {
      $driver = new Driver($entityManager);
      $metadataConfig = $driver->get(Metadata::class)->getMetadataConfig();
      $cache->set('GraphQL', $metadataConfig);
  } else {
      // The second parameter is the Config object
      $driver = new Driver($entityManager, null, $metadataConfig);
  }
