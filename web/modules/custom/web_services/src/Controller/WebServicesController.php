<?php

namespace Drupal\web_services\Controller;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\user\Entity\User;
use Drupal\user\Entity\Role;

class WebServicesController
{
  /**Funcion que consulta información de un rol por el parametro de nombre */
  
  public function getRolesByName($nombre = '') {

  $rol_names = user_role_names();
  $roles = $this->get_matches_array_roles($nombre, $rol_names);

  $user_array = [];
    
  foreach ($roles as $key => $value){
      
      $final_array = [];
      
      $entity_rol = Role::load($value['key']);

      $permissions_array = [];

      if(!$entity_rol->isAdmin()) {
 
      $permissions =  $entity_rol->getPermissions();

      $permissions_array = implode(",", $permissions);

      foreach($permissions as $item){

      $final_array[] = [
      'nombrePermiso' => $item,
      'idPermiso' => $item,
      ];
      } 
      } 

      $user_array[] = [
      "nombre" => $value['value'],
      "id" => $value['key'],
      "permisos" => $final_array
      ];
    }
   
    //Transforma un array en una respuesta Json.
    $response = new JsonResponse($user_array);

    return $response;
  }



  function get_role_by_name($rol_name, $rol_names) {

    foreach($rol_names as $key => $value){
      
      if($rol_name == $key){ return $value;}
    }
    return '';
  }
  function get_matches_array_roles($name, $rol_names) {

  $array_matchs_roles = [];

  foreach($rol_names as $key => $value){
      // array_push($array_matchs_roles,$value);
      if (false !== stripos($value, $name)) {
      $array_matchs_roles[]=[
        'key' => $key,
        'value' => $value
      ];
      }
    }
    return $array_matchs_roles;
  }

}