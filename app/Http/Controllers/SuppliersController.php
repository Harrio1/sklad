<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Suppliers;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;

// Контроллер для управления действиями, связанными с поставщиками
class SuppliersController extends Controller
{
    // Метод для добавления нового поставщика
    public function store(Request $request)
    {
        // Валидация входных данных
        $validated = $request->validate([
            'supplierName' => ['required', 'max:50'],
            'address' => ['required', 'max:100'],
            'supplierComments' => ['required', 'max:500'],
            'phoneNumber' => ['required', 'integer'],
        ]);

        // Шаблон для проверки номера телефона (должен содержать 11 цифр)
        $pattern = '/^\d{11}$/';

        // Проверка номера телефона на соответствие шаблону
        if (preg_match($pattern, $request->phoneNumber)) {
            // Создание нового объекта поставщика и сохранение в базе данных
            $suppliers = new Suppliers;
            $suppliers->name = $request->supplierName;
            $suppliers->address = $request->address;
            $suppliers->comments = $request->supplierComments;
            $suppliers->phone = $request->phoneNumber;
            $suppliers->save();
            // Возвращаем успешный ответ
            return Response::json(['status' => 'Данные добавлены', 'isOk' => True], 200);
        } else {
            // Возвращаем ответ об ошибке, если номер телефона недействителен
            return Response::json(['status' => 'Номер телефона недействителен.','isOk' => False], 200);
        }
    }

    // Метод для получения списка всех поставщиков
    public function getSuppliers(){
        // Получаем всех поставщиков из базы данных
        $suppliers = Suppliers::all();
        // Возвращаем список поставщиков в формате JSON
        return Response::json(['suppliers' => $suppliers], 200);
    }

    // Метод для удаления поставщика по ID
    public function deleteById(Request $request){
        // Поиск поставщика по ID, если не найден - возвращаем ошибку
        $flight = Suppliers::findOr($request->suppliers_id, function () {
              return Response::json(['status' => 'Ошибка, элемент не найден'], 200);
        });
        // Удаление поставщика
        Suppliers::destroy($request->suppliers_id);
        // Возвращаем успешный ответ
        return Response::json(['status' => 'Поставщик успешно удален'], 200);
    }

    // Метод для обновления данных поставщика по ID
    public function updateById(Request $request){
        // Поиск поставщика по ID, если не найден - возвращаем ошибку
        $flight = Suppliers::findOr($request->supplierId, function () {
            return Response::json(['status' => 'Ошибка, элемент не найден', 'isOk' => False], 200);
        });
        // Шаблон для проверки номера телефона (должен содержать 11 цифр)
        $pattern = '/^\d{11}$/';

        // Проверка номера телефона на соответствие шаблону
        if (preg_match($pattern, $request->phoneNumber)) {
            // Обновление данных поставщика и сохранение в базе данных
            $flight->name = $request->supplierName;
            $flight->address = $request->address;
            $flight->comments = $request->supplierComments;
            $flight->phone = $request->phoneNumber;
            $flight->save();
            // Возвращаем успешный ответ
            return Response::json(['status' => 'Поставщик успешно изменен', 'isOk' => True], 200);
        } else {
            // Возвращаем ответ об ошибке, если номер телефона недействителен
            return Response::json(['status' => 'Номер телефона недействителен.','isOk' => False], 200);
        }
    }
}
