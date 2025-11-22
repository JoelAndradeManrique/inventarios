<?php
require_once '../config/db.php';
require_once '../components/header.php'; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimientos</title>
    <style>
        body { background: #f7f7f7; margin: 0; font-family: Arial, sans-serif; }
        .container { width: 95%; margin: 25px auto; max-width: 1200px; }
        h2 { color: #444; }

        .btn { background: #f5d04c; border: none; padding: 10px 18px; border-radius: 6px; font-weight: bold; cursor: pointer; }
        .btn:hover { background: #e2be43; }
        .top-buttons { display: flex; justify-content: space-between; margin-bottom: 15px; gap: 10px;}

        /* TABLA PRINCIPAL */
        table { width: 100%; background: white; border-collapse: collapse; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.08); }
        th { background: #1e1e2f; color: white; padding: 10px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        
        /* MODAL (PUNTO DE VENTA) */
        .modal-bg { position: fixed; inset: 0; background: rgba(0,0,0,0.45); display: none; justify-content: center; align-items: center; z-index: 1000; }
        .modal { width: 700px; background: white; padding: 25px; border-radius: 12px; max-height: 90vh; display: flex; flex-direction: column; }
        
        /* SELECTORES CASCADA */
        .pos-controls { display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; margin-bottom: 15px; align-items: end;}
        .pos-controls label { font-size: 12px; font-weight: bold; display: block; margin-bottom: 5px;}
        .pos-controls select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; }
        
        /* Tabla del Carrito */
        .cart-container { flex:1; overflow-y:auto; min-height: 200px; border:1px solid #eee; padding:0; border-radius: 6px;}
        .cart-table { width: 100%; border-collapse: collapse; }
        .cart-table th { background: #eee; color: #333; padding: 10px; font-size: 14px; position: sticky; top: 0;}
        .cart-table td { border-bottom: 1px solid #eee; padding: 10px; }
        
        .total-box { font-size: 24px; font-weight: bold; text-align: right; margin-top: 15px; color: #2ecc71; }
        .modal-buttons { margin-top: 20px; display: flex; justify-content: space-between; }
        .btn-cancel { background: #ccc; }
        .btn-add { background: #3498db; color: white; height: 40px;} 
        .btn-remove { background: #ff6b6b; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 12px;}

        /* TOAST */
        .toast { visibility: hidden; min-width: 250px; background-color: #333; color: #fff; text-align: center; border-radius: 8px; padding: 16px; position: fixed; z-index: 2001; right: 30px; top: 30px; opacity: 0; transition: opacity 0.5s, top 0.5s; }
        .toast.show { visibility: visible; opacity: 1; top: 50px; }
        .toast.success { background-color: #4caf50; }
        .toast.error { background-color: #e63946; }
        .toast.warning { 
            background-color: #ff9800; 
            color: white; 
            width: 350px; 
        }
        
        /* Toast Rojo Oscuro/Negro (Crítico - Stock 0) */
        .toast.critical {
            background-color: #c0392b; /* Rojo fuerte */
            color: white;
            width: 350px;
            font-weight: bold;
            box-shadow: 0 0 15px rgba(192, 57, 43, 0.5); /* Resplandor rojo */
        }
    </style>
</head>
<body>

    <div id="toast" class="toast">Mensaje</div>
    <div id="toastStock" class="toast">Alerta de Stock</div>
    <div class="container">
        <h2>Historial de Movimientos</h2>

        <div class="top-buttons">
            <div>
                <button class="btn" id="btnNuevaVenta" style="background:#2ecc71; color:white;">+ Nueva Venta</button>
                <button class="btn" id="btnNuevaCompra" style="margin-left: 10px;">+ Registrar Compra</button>
            </div>
            <button class="btn" id="btnCorte" style="background:#34495e; color:white;">✂ Hacer Corte de Caja</button>
        </div>

        <table id="tablaHistorial">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Folio</th>
                    <th>Tipo</th>
                    <th>Producto</th>
                    <th>Cant.</th>
                    <th>Precio</th>
                    <th>Usuario</th>
                </tr>
            </thead>
            <tbody id="tbodyHistorial"></tbody>
        </table>
    </div>

    <div class="modal-bg" id="modalBg">
        <div class="modal">
            <h3 id="modalTitle">Nueva Venta</h3>
            
            <div class="pos-controls">
                <div>
                    <label>1. Categoría</label>
                    <select id="selCategoria">
                        <option value="">Cargando...</option>
                    </select>
                </div>
                <div>
                    <label>2. Producto</label>
                    <select id="selProducto" disabled>
                        <option value="">Selecciona una categoría primero</option>
                    </select>
                </div>
                <button class="btn btn-add" id="btnAgregarItem">+ Agregar</button>
            </div>

            <div class="cart-container">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Cant.</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="tbodyCarrito">
                        </tbody>
                </table>
            </div>

            <div class="total-box">
                Total: $<span id="totalVenta">0.00</span>
            </div>

            <div class="modal-buttons">
                <button class="btn btn-cancel" id="cancelar">Cancelar</button>
                <button class="btn" id="btnProcesar">Procesar Movimiento</button>
            </div>
        </div>
    </div>

    <script>
        let carrito = [];
        let tipoMovimiento = 'SALIDA'; 
        let productosCache = []; // Para no llamar a la API a cada rato

        function showToast(message, type = 'success') {
            const toast = document.getElementById("toast");
            toast.textContent = message;
            toast.className = "toast show " + type;
            setTimeout(() => { toast.className = toast.className.replace("show", ""); }, 3000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            cargarHistorial();
            cargarCategorias();
        });

        // --- 1. CARGAR HISTORIAL ---
        async function cargarHistorial() {
            try {
                const res = await fetch('../api/movimientos.php');
                const datos = await res.json();
                const tbody = document.getElementById('tbodyHistorial');
                tbody.innerHTML = '';

                datos.forEach(mov => {
                    const tr = document.createElement('tr');
                    const precio = mov.tipo_movimiento === 'SALIDA' ? mov.precio_venta_momento : mov.precio_compra_momento;
                    tr.innerHTML = `
                        <td>${mov.fecha_movimiento}</td>
                        <td>${mov.folio_transaccion}</td>
                        <td style="color:${mov.tipo_movimiento === 'SALIDA' ? 'red' : 'green'}; font-weight:bold;">
                            ${mov.tipo_movimiento}
                        </td>
                        <td>${mov.producto}</td>
                        <td>${mov.cantidad}</td>
                        <td>$${precio}</td>
                        <td>${mov.usuario}</td>
                    `;
                    tbody.appendChild(tr);
                });
            } catch (error) { console.error(error); }
        }

        // --- 2. CARGAR CATEGORÍAS (Para el Select 1) ---
        async function cargarCategorias() {
            const res = await fetch('../api/categorias.php');
            const cats = await res.json();
            const sel = document.getElementById('selCategoria');
            sel.innerHTML = '<option value="">Seleccione Categoría</option>';
            cats.forEach(c => {
                sel.innerHTML += `<option value="${c.id_categoria}">${c.nombre}</option>`;
            });
        }

        // --- 3. CARGAR PRODUCTOS AL CAMBIAR CATEGORÍA ---
        document.getElementById('selCategoria').addEventListener('change', async (e) => {
            const idCat = e.target.value;
            const selProd = document.getElementById('selProducto');
            
            selProd.innerHTML = '<option>Cargando...</option>';
            selProd.disabled = true;

            if(idCat) {
                const res = await fetch(`../api/productos.php?categoria=${idCat}`);
                productosCache = await res.json(); // Guardamos en memoria
                
                selProd.innerHTML = '<option value="">Seleccione Producto</option>';
                
                if(productosCache.length > 0) {
                    productosCache.forEach(p => {
                        // Mostramos Nombre y Stock actual
                        selProd.innerHTML += `<option value="${p.id_producto}">${p.nombre} (Stock: ${p.stock_actual})</option>`;
                    });
                    selProd.disabled = false;
                } else {
                    selProd.innerHTML = '<option>Sin productos en esta categoría</option>';
                }
            } else {
                selProd.innerHTML = '<option>Seleccione una categoría primero</option>';
            }
        });

        // --- 4. AGREGAR AL CARRITO ---
        document.getElementById('btnAgregarItem').addEventListener('click', () => {
            const idProd = document.getElementById('selProducto').value;
            if(!idProd) return;

            // Buscamos el objeto completo en el cache
            const prod = productosCache.find(p => p.id_producto == idProd);
            if(!prod) return;

            // Verificar si ya está
            const existente = carrito.find(item => item.id_producto === prod.id_producto);
            
            // Definir precio según tipo de movimiento
            const precioUsar = tipoMovimiento === 'SALIDA' ? prod.precio_venta : prod.precio_compra;

            if (existente) {
                existente.cantidad++;
            } else {
                carrito.push({
                    id_producto: prod.id_producto,
                    nombre: prod.nombre,
                    precio: precioUsar,
                    cantidad: 1,
                    stockMax: prod.stock_actual
                });
            }
            renderizarCarrito();
        });

        function renderizarCarrito() {
            const tbody = document.getElementById('tbodyCarrito');
            tbody.innerHTML = '';
            let total = 0;

            carrito.forEach((item, index) => {
                const subtotal = item.cantidad * item.precio;
                total += subtotal;

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${item.nombre}</td>
                    <td>$${item.precio}</td>
                    <td>
                        <input type="number" value="${item.cantidad}" min="1" 
                               onchange="actualizarCantidad(${index}, this.value)" style="width:60px;">
                    </td>
                    <td>$${subtotal.toFixed(2)}</td>
                    <td><button class="btn-remove" onclick="eliminarDelCarrito(${index})">X</button></td>
                `;
                tbody.appendChild(tr);
            });

            document.getElementById('totalVenta').textContent = total.toFixed(2);
        }

        window.actualizarCantidad = (index, val) => {
            val = parseInt(val);
            if (val < 1) val = 1;
            
            // Validar Stock si es venta
            if (tipoMovimiento === 'SALIDA' && val > carrito[index].stockMax) {
                showToast(`Solo tienes ${carrito[index].stockMax} unidades`, 'error');
                val = carrito[index].stockMax;
            }
            
            carrito[index].cantidad = val;
            renderizarCarrito();
        };

        window.eliminarDelCarrito = (index) => {
            carrito.splice(index, 1);
            renderizarCarrito();
        };

        // --- 5. PROCESAR Y CERRAR MODAL ---
        const modalBg = document.getElementById('modalBg');
        const modalTitle = document.getElementById('modalTitle');
        
        document.getElementById('btnNuevaVenta').onclick = () => abrirModal('SALIDA');
        document.getElementById('btnNuevaCompra').onclick = () => abrirModal('ENTRADA');
        document.getElementById('cancelar').onclick = () => modalBg.style.display = 'none';

        function abrirModal(tipo) {
            tipoMovimiento = tipo;
            carrito = [];
            renderizarCarrito();
            modalTitle.textContent = tipo === 'SALIDA' ? 'Nueva Venta' : 'Registrar Entrada de Mercancía';
            
            // Limpiar selects
            document.getElementById('selCategoria').value = "";
            document.getElementById('selProducto').innerHTML = "<option>Seleccione categoría primero</option>";
            document.getElementById('selProducto').disabled = true;
            
            modalBg.style.display = 'flex';
        }

        document.getElementById('btnProcesar').addEventListener('click', async () => {
            if (carrito.length === 0) {
                showToast("El carrito está vacío", 'error');
                return;
            }

            const datosAPI = {
                tipo: tipoMovimiento,
                productos: carrito.map(item => ({ id: item.id_producto, cantidad: item.cantidad }))
            };

            try {
                const res = await fetch('../api/movimientos.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(datosAPI)
                });
                const result = await res.json();

                if (result.success) {
                    showToast(result.message, 'success');
                    modalBg.style.display = 'none';
                    cargarHistorial();
                    
                    // --- LÓGICA DE AVISOS DOBLES ---
                    
                    // 1. AVISO CRÍTICO (Stock 0)
                    if (result.alertas_cero && result.alertas_cero.length > 0) {
                        const prods = result.alertas_cero.join(", ");
                        // Usamos setTimeout para que si salen los dos, no se encimen instantáneamente
                        setTimeout(() => {
                            showCustomToast(`Ups, nos quedamos sin: ${prods}. Debemos pedir más.`, 'critical');
                        }, 500); 
                    }

                    // 2. AVISO ADVERTENCIA (Bajo Stock)
                    if (result.alertas_bajas && result.alertas_bajas.length > 0) {
                        const prods = result.alertas_bajas.join(", ");
                        setTimeout(() => {
                            showCustomToast(`El stock mínimo de: ${prods} bajó. Revisa inventario.`, 'warning');
                        }, 2500); // Sale 2 segundos después del primero para que le de tiempo de leer
                    }

                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) { showToast("Error al procesar", 'error'); }
        });

        // --- 6. FUNCIONALIDAD DE CORTE DE CAJA ---
        document.getElementById('btnCorte').addEventListener('click', async () => {
            if(!confirm("¿Deseas realizar el Corte de Caja ahora? Se cerrará el turno actual.")) return;

            try {
                const res = await fetch('../api/realizar_corte.php');
                const result = await res.json();

                if(result.success) {
                    alert("✅ " + result.message + "\nPuedes ver el detalle en Reportes.");
                    // Opcional: recargar historial para ver si cambian
                } else {
                    alert("⚠️ " + result.message);
                }
            } catch(e) { alert("Error de conexión"); }
        });

        let stockToastTimer;

        function showCustomToast(message, type) {
            const toastStock = document.getElementById("toastStock");
            
            // 1. Si ya había un contador corriendo, lo cancelamos para que no cierre el nuevo mensaje
            if (stockToastTimer) {
                clearTimeout(stockToastTimer);
            }

            // 2. Ponemos el contenido y mostramos
            toastStock.textContent = message;
            toastStock.className = "toast show " + type;
            toastStock.style.top = "120px"; // Posición abajo del verde
            
            // 3. Iniciamos nuevo contador de 8 segundos
            stockToastTimer = setTimeout(() => { 
                toastStock.className = "toast"; // Ocultar
                toastStock.style.top = "30px";  
            }, 8000); 
        }

    </script>
</body>
</html>