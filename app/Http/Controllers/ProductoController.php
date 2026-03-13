<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    private string $disk = 'b2';

    // ── Listado ───────────────────────────────────────────────────────────────
    public function listado()
    {
        $productos = Producto::paginate(24);

        return response()->json($productos->through(
            fn($p) => $this->formatear($p)
        ));
    }

    // ── Detalle ───────────────────────────────────────────────────────────────
    public function obtener($id)
    {
        $producto = Producto::findOrFail($id);

        return response()->json($this->formatear($producto));
    }

    // ── Agregar ───────────────────────────────────────────────────────────────
    public function agregar(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'foto'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $fotoKey = null;

        if ($request->hasFile('foto')) {
            $fotoKey = $request->file('foto')->store('productos/fotos', $this->disk);
        }

        $producto = Producto::create([
            'nombre'      => $request->nombre,
            'descripcion' => $request->descripcion,
            'foto_key'    => $fotoKey,
        ]);

        return response()->json($this->formatear($producto), 201);
    }

    // ── Modificar ─────────────────────────────────────────────────────────────
    public function modificar(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $request->validate([
            'nombre'      => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'foto'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        // Si sube foto nueva, borrar la anterior
        if ($request->hasFile('foto')) {
            if ($producto->foto_key) {
                Storage::disk($this->disk)->delete($producto->foto_key);
            }
            $producto->foto_key = $request->file('foto')->store('productos/fotos', $this->disk);
        }

        $producto->nombre      = $request->nombre;
        $producto->descripcion = $request->descripcion;
        $producto->save();

        return response()->json($this->formatear($producto));
    }

    // ── Eliminar (Soft Delete) ────────────────────────────────────────────────
    public function eliminar($id)
    {
        $producto = Producto::findOrFail($id);
        $producto->delete(); // SoftDelete — no borra físicamente

        return response()->json(['message' => 'Producto eliminado correctamente.']);
    }

    // ── Helper privado ────────────────────────────────────────────────────────
    private function formatear(Producto $producto): array
    {
        return [
            'id'          => $producto->id,
            'nombre'      => $producto->nombre,
            'descripcion' => $producto->descripcion,
            'foto_url'    => $producto->foto_key
                ? Storage::disk($this->disk)->url($producto->foto_key)
                : null,
            'created_at'  => $producto->created_at,
        ];
    }
}