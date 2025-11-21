<?php
require_once '../config/db.php';
require_once '../components/header.php'; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inventarios</title>

    <style>
        body { background: #f4f4f4; margin: 0; font-family: Arial, sans-serif; }
        .container { width: 95%; margin: 25px auto; max-width: 1200px; }
        h2 { color: #444; }

        .btn { background: #f5d04c; border: none; padding: 10px 18px; border-radius: 6px; font-weight: bold; cursor: pointer; }
        .btn:hover { background: #e2be43; }
        .btn-danger { background: #ff6b6b; color: white; margin-top: 10px;}

        /* TABLA */
        table { width: 100%; background: white; border-collapse: collapse; margin-top: 15px; border-radius: 10px; overflow: hidden; box-shadow: 0 3px 10px rgba(0,0,0,0.09); }
        th { background: #1e1e2f; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #eee; cursor: pointer; }
        tr.producto:hover { background: #f5f5f5; }
        
        /* ALERTA DE STOCK */
        tr.stock-bajo td { color: #c62828; font-weight: bold; }
        tr.stock-bajo { background-color: #ffebee; }

        /* FILA EXPANDIDA CON FOTO */
        .expand-row { display: none; background: #fafafa; padding: 15px; border-left: 4px solid #f5d04c; }
        .expand-row.visible { display: table-row; }
        
        .detail-content { padding: 15px; display: flex; justify-content: space-between; align-items: flex-start; }
        .detail-info { flex: 1; }
        
        /* Estilo de la imagen en la tabla */
        .product-image-box { 
            width: 120px; 
            height: 120px; 
            background: #fff; /* Fondo blanco para que se vea limpio */
            border-radius: 8px; 
            
            /* ESTO EVITA QUE SOBRESALGA */
            overflow: hidden; 
            
            display: flex; 
            justify-content: center; 
            align-items: center;
            border: 1px solid #ddd;
            margin-left: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); /* Un poco de sombra */
        }

        .product-image-box img { 
            width: 100%; 
            height: 100%; 
            
            /* CLAVE: 'contain' muestra toda la foto sin cortarla. 
               Si prefieres que llene todo el cuadro aunque se corte, usa 'cover' */
            object-fit: contain; 
            display: block;
        }
        .no-image { color: #999; font-size: 12px; text-align: center; }

        /* MODAL */
        .modal-bg { position: fixed; inset: 0; background: rgba(0,0,0,0.45); display: none; justify-content: center; align-items: center; z-index: 1000; }
        .modal { width: 500px; background: white; padding: 25px; border-radius: 12px; max-height: 90vh; overflow-y: auto; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        label { font-weight: bold; display: block; margin-top: 12px; font-size: 14px; }
        input, select { width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #ccc; box-sizing: border-box;}
        .total-box { margin-top: 15px; font-size: 18px; font-weight: bold; text-align: right; color: #333; }
        .modal-buttons { margin-top: 20px; display: flex; justify-content: space-between; }
        .btn-cancel { background: #ccc; }
        
        /* TOAST */
        .toast { visibility: hidden; min-width: 250px; background-color: #333; color: #fff; text-align: center; border-radius: 8px; padding: 16px; position: fixed; z-index: 1001; right: 30px; top: 30px; opacity: 0; transition: opacity 0.5s, top 0.5s; }
        .toast.show { visibility: visible; opacity: 1; top: 50px; }
        .toast.success { background-color: #4caf50; }
        .toast.error { background-color: #e63946; }
    </style>
</head>
<body>

    <div id="toast" class="toast">Mensaje</div>

    <div class="container">
        <h2>Gestión de Inventarios</h2>

        <button class="btn" id="btnAdd">Añadir producto</button>
        <input type="text" id="searchBar" placeholder="Buscar por nombre o SKU..." 
               style="margin-top:15px; padding:10px; width:300px; border-radius:6px; border:1px solid #ccc;">

        <table id="tablaProductos">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Contenido</th>
                    <th>P. Venta</th>
                    <th>Stock</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody id="tbodyProductos"></tbody>
        </table>
    </div>

    <div class="modal-bg" id="modalBg">
        <div class="modal">
            <h3>Añadir producto</h3>
            
            <form id="prodForm">
                <label>Categoría</label>
                <select name="id_categoria" id="categoriaSelect" required>
                    <option value="">Cargando...</option>
                </select>

                <label>Nombre del producto</label>
                <input type="text" name="nombre" id="nombre" required placeholder="Ej: Coca Cola">

                <label>Imagen del Producto (Opcional)</label>
                <input type="file" name="imagen" id="imagenInput" accept="image/*">

                <div class="form-grid">
                    <div>
                        <label>Contenido Neto</label>
                        <input type="text" name="contenido_neto" id="contenido" required placeholder="Ej: 600ml">
                    </div>
                    <div>
                        <label>Stock Mínimo</label>
                        <input type="number" name="stock_minimo" id="stockMin" required value="5">
                    </div>
                </div>

                <div class="form-grid">
                    <div>
                        <label>Precio Compra</label>
                        <input type="number" name="precio_compra" id="precioCompra" required step="0.50">
                    </div>
                    <div>
                        <label>Precio Venta</label>
                        <input type="number" name="precio_venta" id="precioVenta" required step="0.50">
                    </div>
                </div>

                <label>Stock Inicial</label>
                <input type="number" name="stock_actual" id="stockActual" required value="0">

                <div class="total-box">
                    Valor invertido: $<span id="totalInversion">0.00</span>
                </div>

                <div class="modal-buttons">
                    <button type="button" class="btn btn-cancel" id="cancelar">Cancelar</button>
                    <button type="submit" class="btn">Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showToast(message, type = 'success') {
            const toast = document.getElementById("toast");
            toast.textContent = message;
            toast.className = "toast show " + type;
            setTimeout(() => { toast.className = toast.className.replace("show", ""); }, 3000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            cargarCategorias();
            cargarProductos();
        });

        async function cargarCategorias() {
            const res = await fetch('../api/categorias.php');
            const cats = await res.json();
            const select = document.getElementById('categoriaSelect');
            select.innerHTML = '<option value="">Seleccione...</option>';
            cats.forEach(c => {
                select.innerHTML += `<option value="${c.id_categoria}">${c.nombre}</option>`;
            });
        }

        async function cargarProductos(busqueda = '') {
            try {
                const res = await fetch(`../api/productos.php?busqueda=${busqueda}`);
                const productos = await res.json();
                renderizarTabla(productos);
            } catch (error) {
                console.error(error);
            }
        }

        function renderizarTabla(productos) {
            const tbody = document.getElementById('tbodyProductos');
            tbody.innerHTML = '';

            productos.forEach(prod => {
                const tr = document.createElement('tr');
                tr.className = 'producto ' + (prod.alerta ? 'stock-bajo' : '');
                tr.innerHTML = `
                    <td><strong>${prod.codigo_sku}</strong></td>
                    <td>${prod.nombre}</td>
                    <td>${prod.nombre_categoria}</td>
                    <td>${prod.contenido_neto}</td>
                    <td>$${prod.precio_venta}</td>
                    <td>${prod.stock_actual}</td>
                    <td>${prod.alerta ? '⚠️' : '✅'}</td>
                `;

                // Lógica de Imagen: Si tiene imagen en BD, usamos esa ruta. Si no, null.
                const imgUrl = prod.imagen ? `../uploads/${prod.imagen}` : null;
                const imgHtml = imgUrl 
                    ? `<img src="${imgUrl}" alt="Foto">` 
                    : `<div class="no-image">Sin foto</div>`;

                const trDetail = document.createElement('tr');
                trDetail.className = 'expand-row';
                trDetail.innerHTML = `
                    <td colspan="7">
                        <div class="detail-content">
                            <div class="detail-info">
                                <strong>Detalles Financieros:</strong><br>
                                Costo Compra: $${prod.precio_compra}<br>
                                Margen: $${(prod.precio_venta - prod.precio_compra).toFixed(2)}<br>
                                Stock Mínimo: ${prod.stock_minimo}
                                <br><br>
                                <?php if ($rol === 'ADMIN'): ?>
                                <button class="btn btn-danger" onclick="eliminarProducto(${prod.id_producto})">Eliminar</button>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-image-box">
                                ${imgHtml}
                            </div>
                        </div>
                    </td>
                `;

                tr.addEventListener('click', () => {
                    trDetail.classList.toggle('visible');
                });

                tbody.appendChild(tr);
                tbody.appendChild(trDetail);
            });
        }

        // Buscador
        const searchBar = document.getElementById('searchBar');
        let debounceTimer;
        searchBar.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                cargarProductos(e.target.value);
            }, 300);
        });

        // Modal Logic
        const modalBg = document.getElementById('modalBg');
        const btnAdd = document.getElementById('btnAdd');
        const btnCancel = document.getElementById('cancelar');
        const form = document.getElementById('prodForm');

        btnAdd.onclick = () => {
            form.reset();
            document.getElementById('totalInversion').textContent = "0.00";
            modalBg.style.display = 'flex';
        };
        btnCancel.onclick = () => modalBg.style.display = 'none';

        // Calculadora total
        const stockInput = document.getElementById('stockActual');
        const precioInput = document.getElementById('precioCompra');
        function calcTotal() {
            const total = (stockInput.value * precioInput.value) || 0;
            document.getElementById('totalInversion').textContent = total.toFixed(2);
        }
        stockInput.oninput = precioInput.oninput = calcTotal;

        // --- ENVÍO DEL FORMULARIO (CAMBIO IMPORTANTE) ---
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Usamos FormData para empaquetar Textos + Archivo automáticamente
            const formData = new FormData(form);

            try {
                const res = await fetch('../api/productos.php', {
                    method: 'POST',
                    // IMPORTANTE: Al usar FormData, NO ponemos 'Content-Type': 'application/json'
                    // El navegador detecta que es multipart automáticamente.
                    body: formData
                });
                const result = await res.json();

                if(result.success) {
                    showToast(result.message, 'success');
                    modalBg.style.display = 'none';
                    cargarProductos(); 
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                console.error(error);
                showToast("Error al guardar", 'error');
            }
        });

        window.eliminarProducto = async (id) => {
            if(!confirm("¿Eliminar producto?")) return;
            try {
                const res = await fetch('../api/productos.php', {
                    method: 'DELETE',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ id_producto: id })
                });
                const result = await res.json();
                if(result.success) {
                    showToast(result.message, 'success');
                    cargarProductos();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast("Error al eliminar", 'error');
            }
        };
    </script>
</body>
</html>