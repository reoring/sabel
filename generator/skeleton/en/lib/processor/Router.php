<?php

/**
 * Processor_Router
 *
 * @category   Processor
 * @package    lib.processor
 * @author     Mori Reo <mori.reo@sabel.jp>
 * @copyright  2004-2008 Mori Reo <mori.reo@sabel.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Processor_Router extends Sabel_Bus_Processor
{
  public function execute(Sabel_Bus $bus)
  {
    $request = $bus->get("request");
    $config = $bus->getConfig("map");
    $config->configure();
    
    if ($candidate = $config->getValidCandidate($request->getUri())) {
      $request->setParameterValues(array_map("urldecode", $candidate->getUriParameters()));
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
