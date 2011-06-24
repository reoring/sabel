<?php

class TestProcessor_Router extends Sabel_Bus_Processor
{
  public function execute(Sabel_Bus $bus)
  {
    $request = $bus->get("request");
    $config = $bus->getConfig("map");
    $config->configure();
    
    if ($candidate = $config->getValidCandidate($request->getUri())) {
      $request->setParameterValues($candidate->getUriParameters());
      $destination = $candidate->getDestination();
      l("DESTINATION: " . $destination);
      $bus->set("destination", $destination);
      Sabel_Context::getContext()->setCandidate($candidate);
    } else {
      $message = __METHOD__ . "() didn't match to any routing configuration.";
      throw new Sabel_Exception_Runtime($message);
    }
  }
}
