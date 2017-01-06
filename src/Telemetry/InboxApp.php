<?php

namespace Telemetry;

class InboxApp extends \Silex\Application {

  const VERSION = "1.0";

  private $repo;

  public function __construct(Repository $repo) {
    parent::__construct();
    $this->repo = $repo;

    // Index.
    $this->get('/', function() {
      return $this->index();
    });

    // Count.
    $this->get('/count', function () {
      $count = $this->repo->count();
      return $this->json(['count' => $count], 200);
    });

    // Summary.
    $this->get('/summary', function () {
      $summarizer = new Summarizer(['id']);
      foreach ($this->repo->arrays() as $array) {
        $summarizer->add($array);
      }
      return $this->json(['summary' => $summarizer->getSummary()]);
    });

    $this->get('/ping', function(Silex\Application $app) {
      return $this->json([], 204);
    });

    $this->post('/submit/{id}', function($id) {
      try {
        $this->repo->add($id, 'php://input');
        return $this->json(['id' => $id], 200);
      } catch (RepositoryException $e) {
        return $this->json([
          'id' => $id,
          'message' => $e->getMessage(),
        ], $e->getCode());
      }
    });

    $this->error(function(\Exception $e, $code) {
      return $this->json([
        'error' => $e->getMessage()
      ], 500);
    });
  }

  public function index() {
    if (is_dir(__DIR__ . '/../.git')) {
      exec('git rev-parse --short --verify HEAD', $output);
      return $output[0];
    } else {
      return self::VERSION;
    }
  }
}
