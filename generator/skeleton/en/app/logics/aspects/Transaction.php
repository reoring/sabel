<?php

class Logics_Aspects_Transaction implements Sabel_Aspect_MethodInterceptor
{
  public function invoke(Sabel_Aspect_MethodInvocation $inv)
  {
    if (!$active = Sabel_Db_Transaction::isActive()) {
      Sabel_Db_Transaction::activate();
    }
    
    try {
      $result = $inv->proceed();
      
      if (!$active) {
        Sabel_Db_Transaction::commit();
      }
      
      return $result;
    } catch (Exception $e) {
      if (!$active) {
        Sabel_Db_Transaction::rollback();
      }
      
      throw $e;
    }
  }
}
