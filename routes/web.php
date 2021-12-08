<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use App\Http\Middleware\RequestTokenAuth;
use App\Http\Middleware\PublicMiddleware;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

Route::get(
    '/user/activate/{code}',
    'AuthController@activation'
);

// API route group
$router->group(['prefix' => 'api/v1', 'middleware' => RequestTokenAuth::class], function () use ($router) {
    // // Credential
    // $router->get('login/a', 'AuthController@index');
    // $router->get('login/a/{id}', 'AuthController@index');
    // $router->post('register', 'AuthController@registerAdmin');
    // $router->post('login', 'AuthController@auth');




    // Student
    $router->get('students', 'Client\StudentsController@index');
    $router->get('students/{id}', 'Client\StudentsController@index');
    $router->post('students', 'Client\StudentsController@store');
    $router->put('students/{id}', 'Client\StudentsController@update');
    $router->delete('students/{id}', 'Client\StudentsController@destroy');

    // Teacher
    $router->get('teachers', 'Client\TeachersController@index');
    $router->get('teachers/{id}', 'Client\TeachersController@index');
    $router->post('teachers', 'Client\TeachersController@store');
    $router->put('teachers/{id}', 'Client\TeachersController@update');
    $router->delete('teachers/{id}', 'Client\TeachersController@destroy');
});

// Master API Group Routes
$router->group(['prefix' => 'api/v1/master', 'middleware' => PublicMiddleware::class], function () use ($router) {
    // Province
    $router->get('provinces', 'Master\ProvincesController@index');
    $router->get('provinces/{id}', 'Master\ProvincesController@index');
    $router->post('provinces', 'Master\ProvincesController@store');
    $router->put('provinces/{id}', 'Master\ProvincesController@update');
    $router->delete('provinces/{id}', 'Master\ProvincesController@destroy');

    // Regencies
    $router->get('regencies', 'Master\RegenciesController@index');
    $router->get('regencies/{id}', 'Master\RegenciesController@index');
    $router->post('regencies', 'Master\RegenciesController@store');
    $router->put('regencies/{id}', 'Master\RegenciesController@update');
    $router->delete('regencies/{id}', 'Master\RegenciesController@destroy');

    // Districts
    $router->get('districts', 'Master\DistrictsController@index');
    $router->get('districts/{id}', 'Master\DistrictsController@index');
    $router->post('districts', 'Master\DistrictsController@store');
    $router->put('districts/{id}', 'Master\DistrictsController@update');
    $router->delete('districts/{id}', 'Master\DistrictsController@destroy');
    $router->delete('districts/{id}', 'Master\DistrictsController@destroy');

    // Districts
    $router->get('villages', 'Master\VillagesController@index');
    $router->get('villages/{id}', 'Master\VillagesController@index');
    $router->post('villages', 'Master\VillagesController@store');
    $router->put('villages/{id}', 'Master\VillagesController@update');
    $router->delete('villages/{id}', 'Master\VillagesController@destroy');
    $router->delete('villages/{id}', 'Master\VillagesController@destroy');

    // Subject
    $router->get('subjects', 'Master\SubjectsController@index');
    $router->get('subjects/{id}', 'Master\SubjectsController@index');
    $router->post('subjects', 'Master\SubjectsController@store');
    $router->put('subjects/{id}', 'Master\SubjectsController@update');
    $router->delete('subjects/{id}', 'Master\SubjectsController@destroy');

    // Subject Categories
    $router->get('subject/categories', 'Master\SubjectsController@index');
    $router->get('subject/categories/{id}', 'Master\SubjectsController@index');
    $router->post('subject/categories', 'Master\SubjectsController@store');
    $router->put('subject/categories/{id}', 'Master\SubjectsController@update');
    $router->delete('subject/categories/{id}', 'Master\SubjectsController@destroy');

    // Subject Statuses
    $router->get('subject/  ', 'Master\SubjectsController@index');
    $router->get('subject/statuses/{id}', 'Master\SubjectsController@index');
    $router->post('subject/statuses', 'Master\SubjectsController@store');
    $router->put('subject/statuses/{id}', 'Master\SubjectsController@update');
    $router->delete('subject/statuses/{id}', 'Master\SubjectsController@destroy');

    // Class
    $router->get('classes', 'Master\ClassesController@index');
    $router->get('whereUserAuth', 'Master\ClassesController@whereUserAuth');
    $router->get('classes/{id}', 'Master\ClassesController@index');
    $router->post('classes', 'Master\ClassesController@store');
    $router->put('classes/{id}', 'Master\ClassesController@update');
    $router->delete('classes/{id}', 'Master\ClassesController@destroy');

    // Courses
    $router->get('courseWhere/{idx}', 'Master\CoursesController@whereClassId');
    // $router->get('courseWhere/{idx}/{id}', 'Master\CoursesController@whereClassIdUrl');
    // $router->get('whereClassID/{idx}', 'Master\ClassesController@whereClassID');
    $router->get('courses', 'Master\CoursesController@index');
    $router->get('courses/{id}', 'Master\CoursesController@index');
    $router->put('courses/{id}', 'Master\CoursesController@update');
    $router->post('courses', 'Master\CoursesController@store');
    $router->delete('courses/{id}', 'Master\CoursesController@destroy');

    // Task
    $router->get('task', 'Master\TaskController@index');
    $router->get('task/{id}', 'Master\TaskController@index');
    $router->get('whereclassid/{mstClassId}', 'Master\TaskController@whereClassId');
    $router->get('wherePengerjaanId/{mstTaskId}', 'Master\TaskController@wherePengerjaanId');
    $router->post('task', 'Master\TaskController@store');
    $router->put('task/{id}', 'Master\TaskController@update');
    $router->delete('task/{id}', 'Master\TaskController@destroy');

    // type task
    $router->get('typetask', 'Master\TypeTaskController@index');
    $router->get('typetask/{id}', 'Master\TypetaskController@index');
    $router->post('typetask', 'Master\TypetaskController@store');
    $router->put('typetask/{id}', 'Master\TypetaskController@update');
    $router->delete('typetask/{id}', 'Master\TypetaskController@destroy');

    // Soal
    $router->get('soal', 'Master\SoalController@index');
    $router->get('soal/{id}', 'Master\SoalController@index');
    $router->post('soal', 'Master\SoalController@store');
    $router->put('soal/{id}', 'Master\SoalController@update');
    $router->delete('soal/{id}', 'Master\SoalController@destroy');

    // Detail Soal
    $router->get('detailsoal', 'Master\DetailSoalController@index');
    $router->get('detailsoal/{id}', 'Master\DetailSoalController@index');
    $router->post('detailsoal', 'Master\DetailSoalController@store');
    $router->put('detailsoal/{id}', 'Master\DetailSoalController@update');
    $router->delete('detailsoal/{id}', 'Master\DetailSoalController@destroy');

    // Pengerjaan
    $router->get('pengerjaan', 'Master\PengerjaanController@index');
    $router->get('pengerjaan/{id}', 'Master\PengerjaanController@index');
    $router->post('pengerjaan', 'Master\PengerjaanController@store');
    $router->put('pengerjaan/{id}', 'Master\PengerjaanController@update');
    $router->delete('pengerjaan/{id}', 'Master\PengerjaanController@destroy');

    // Detail Pengerjaan
    $router->get('detailpengerjaan', 'Master\DetailPengerjaanController@index');
    $router->get('detailpengerjaan/{id}', 'Master\DetailPengerjaanController@index');
    $router->post('detailpengerjaan', 'Master\DetailPengerjaanController@store');
    $router->put('detailpengerjaan/{id}', 'Master\DetailPengerjaanController@update');
    $router->delete('detailpengerjaan/{id}', 'Master\DetailPengerjaanController@destroy');

    // User Roles
    $router->get('user/roles', 'Master\UserRolesController@index');
    $router->get('user/{email}', 'AuthController@withEmail');
    $router->get('user', 'AuthController@index');
    $router->post('loginheadmaster', 'AuthController@authHeadMaster');

    // type Live session
    $router->get('livesession', 'Master\LiveSesionController@index');
    $router->get('livesession/{id}', 'Master\LiveSesionController@index');
    $router->post('livesession', 'Master\LiveSesionController@store');
    $router->put('livesession/{id}', 'Master\LiveSesionController@update');
    $router->delete('livesession/{id}', 'Master\LiveSesionController@destroy');

    // calendat teacher
    $router->get('calendar', 'Master\CalendarController@index');
    $router->get('calendar/{id}', 'Master\CalendarController@index');
    $router->post('calendar', 'Master\CalendarController@store');
    $router->put('calendar/{id}', 'Master\CalendarController@update');
    $router->delete('calendar/{id}', 'Master\CalendarController@destroy');
});

// Licenses Routes
$router->group(['prefix' => 'api/v1/'], function () use ($router) {

    // Credential
    $router->get('login/a', 'AuthController@index');
    $router->get('login/a/{id}', 'AuthController@index');
    $router->post('register', 'AuthController@registerAdmin');
    $router->post('login', 'AuthController@auth');

    // Province
    $router->get('licenses', 'Client\LicensesController@index');
    $router->get('licenses/{id}', 'Client\LicensesController@index');
    $router->post('licenses', 'Client\LicensesController@store');
    $router->post('licenses/activate', 'Client\LicensesController@activate');
    $router->put('licenses/{id}', 'Client\LicensesController@update');
    $router->delete('licenses/{id}', 'Client\LicensesController@destroy');

    // login head master 


    // cek domain
    $router->post('checkdomain', 'AuthController@checkdomain');
    $router->post('forgetPassword', 'AuthController@forgetPassword');

    // cek aktivation account 
    $router->post('checkAktivationAccount', 'AuthController@checkAktivationAccount');
    $router->post('aktivationAccount', 'AuthController@aktivationAccount');
});
