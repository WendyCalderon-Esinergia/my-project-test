<?php

namespace Drupal\web_services\Controller;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\user\Entity\User;
use Drupal\user\Entity\Role;

class WebServicesController
{

  /**
  * Funcion que consulta información de un rol por el parametro de nombre
  */
  public function getRolesByName($nombre = '') {

    $rol_names = user_role_names();

    $roles = $this->get_matches_array_roles($nombre, $rol_names);
    
    $user_array = [];
    
    foreach ($roles as $key => $value){
      
      $final_array = [];
      
      $entity_rol = Role::load($value['key']);

      $permissions_array = [];

      if(!$entity_rol->isAdmin()) {

        // $final_array[] = [
        //   'nombrePermiso' => array_keys(\Drupal::service('user.permissions')->getPermissions())
        // ];

      // }else{
 
        $permissions =  $entity_rol->getPermissions();

        $permissions_array = implode(",", $permissions);

        foreach($permissions as $item){

          $final_array[] = [
            'nombrePermiso' => t($item),
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

  /**
  * Funcion que consulta roles asignados a un usuario
  */
  public function getRolesByUser($idUsuario = '') {

    $user_array = [];

    $uids = \Drupal::entityQuery('user')
    ->condition('status', 1);
    $or = $uids->orConditionGroup()
      ->condition('field_user_id_sso', $idUsuario)
      ->condition('name',$idUsuario);
    $uids->condition($or);

    $uids = $uids->execute();

    $rol_names = user_role_names();

    $users = User::loadMultiple($uids);
    
    foreach($users as $user){

      $rol_id = $user->get('roles')->getString();

      $array_rol_id = explode(", ", $rol_id);

      foreach($array_rol_id as $id){

        $rol_name = $this->get_role_by_name($id, $rol_names);

        $user_array[] = [
          "id" => $id,
          "nombre" => $rol_name
        ];
      }

    }

    if(!$user_array) {
      $user_array = [];
    }

    //Transforma un array en una respuesta Json.
    $response = new JsonResponse($user_array);

    return $response;
  }

   /**
  * Funcion que consulta información de usuarios de un rol
  */
  public function getUsersByRol($idGrupo = '') {

    $user_array = [];

    $uids = \Drupal::entityQuery('user')
    ->condition('status', 1)
    ->condition('roles', $idGrupo)
    ->sort('created', 'DESC')
    ->execute();

    $users = User::loadMultiple($uids);
    
    foreach($users as $user){

      $user_array[] = [
        "nombre" => $user->get('field_names')->getString(). ' '.$user->get('field_surnames')->getString(),
        "id" => $user->get('field_user_id_sso')->getString(),
        "email" => $user->get('mail')->getString(),
        "documento" => $user->get('field_id')->getString(),
      ];

    }

    //Transforma un array en una respuesta Json.
    $response = new JsonResponse($user_array);

    return $response;
  }

  function get_role_by_name($rol_name, $rol_names) {

    foreach($rol_names as $key => $value){
      
      if($rol_name == $key){
        return $value;
      }
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