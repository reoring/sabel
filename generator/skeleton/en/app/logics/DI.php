<?php

class Logics_DI extends Sabel_Container_Injection
{
  public function configure()
  {
    $this->aspect("Logics_Base")->annotate(
      "transaction", array("Logics_Aspects_Transaction")
    );
  }
}
