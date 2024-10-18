<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\Nomenclatures;

// Контроллер для управления действиями, связанными с номенклатурами
class NomenclaturesController extends Controller
{
    // Метод для добавления новой номенклатуры
    public function store(Request $request)
    {
        // Валидация входных данных
        $validated = $request->validate([
            'name' => ['required', 'max:50'],
            'supplier_id' => ['required', 'integer'],
            'price_per_unit' => ['required', 'numeric', 'min:0'],
            'unit_of_measurement' => ['required', 'in:шт.,кг.,л.'], // Новое правило валидации
        ]);

        // Создание нового объекта номенклатуры
        $nomenclature = new Nomenclatures;

        // Заполнение полей объекта
        $nomenclature->name = $request->name;
        $nomenclature->supplier_id = $request->supplier_id;
        $nomenclature->price_per_unit = $request->price_per_unit;
        $nomenclature->unit_of_measurement = $request->unit_of_measurement; // Новое поле
        $nomenclature->total_quantity = 0;
        $nomenclature->total_price = 0;

        // Сохранение объекта в базе данных
        $nomenclature->save();

        // Возвращаем успешный ответ
        return Response::json(['status' => 'Данные добавлены'], 200);
    }

    // Метод для получения списка всех номенклатур
    public function getNomenclatures()
    {
        // Получаем все номенклатуры с информацией о поставщике
        $nomenclatures = Nomenclatures::with('supplier')->get();
        // Возвращаем список номенклатур в формате JSON
        return Response::json(['nomenclatures' => $nomenclatures], 200);
    }

    // Метод для удаления номенклатуры по ID
    public function deleteById(Request $request)
    {
        // Поиск номенклатуры по ID
        $nomenclature = Nomenclatures::find($request->nomenclature_id);
        if (!$nomenclature) {
            // Возвращаем ошибку, если номенклатура не найдена
            return Response::json(['status' => 'Ошибка, элемент не найден'], 404);
        }

        // Удаление связанных записей в таблице supplies
        $nomenclature->supplies()->delete(); // Предполагается, что у вас есть связь supplies в модели Nomenclatures

        // Удаление номенклатуры
        $nomenclature->delete();
        // Возвращаем успешный ответ
        return Response::json(['status' => 'Номенклатура успешно удалена'], 200);
    }
}
