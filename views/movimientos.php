<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimientos</title>

    <style>
        body {
            background: #f7f7f7;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        /* Contenedor */
        .container {
            width: 95%;
            margin: 25px auto;
        }

        h2 {
            color: #444;
        }

        /* Botones */
        .btn {
            background: #f5d04c;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn:hover {
            background: #e2be43;
        }

        .top-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        /* Tabla */
        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }

        th {
            background: #1e1e2f;
            color: white;
            padding: 10px;
            text-align: left;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        tr:hover {
            background: #f5f5f5;
        }

        /* Modal */
        .modal-bg {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            display: none;
            justify-content: center;
            align-items: center;
        }

        .modal {
            width: 400px;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.25);
        }

        .modal h3 {
            margin-top: 0;
            color: #444;
        }

        label {
            display: block;
            margin-top: 12px;
            font-weight: bold;
            color: #555;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .total-box {
            margin-top: 15px;
            font-size: 18px;
            font-weight: bold;
            color: #333;
            text-align: right;
        }

        .modal-buttons {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }

        .btn-cancel {
            background: #ccc;
        }

        .btn-cancel:hover {
            background: #b1b1b1;
        }
    </style>
</head>
<body>

<!-- AQUÍ VA EL HEADER DINÁMICO -->
<div id="headerContainer"></div>

<script>
    // Cargar header.html
    fetch("../components/header.php")
        .then(r => r.text())
        .then(html => {
            document.getElementById("headerContainer").innerHTML = html;

            // Activar dropdown
            const userInitials = document.getElementById("userInitials");
            const dropdown = document.getElementById("dropdown");

            userInitials.addEventListener("click", () => {
                dropdown.classList.toggle("show");
            });

            window.addEventListener("click", (e) => {
                if (!userInitials.contains(e.target)) {
                    dropdown.classList.remove("show");
                }
            });

            // Activar sección activa "Movimientos"
            const links = document.querySelectorAll(".nav a");
            links.forEach(a => {
                if (a.textContent.trim() === "Movimientos") {
                    a.classList.add("active");
                }
            });
        });
</script>

<!-- CONTENIDO DE LA VISTA -->
<div class="container">
    <h2>Movimientos</h2>

    <div class="top-buttons">
        <button class="btn" id="btnNuevaVenta">Nueva venta</button>
        <button class="btn">Hacer corte</button>
    </div>

    <!-- Tabla de movimientos -->
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Código</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Gas LP</td>
                <td>Energía</td>
                <td>GLP001</td>
                <td>10</td>
                <td>$15</td>
                <td>$150</td>
            </tr>
            <tr>
                <td>Regulador</td>
                <td>Accesorios</td>
                <td>REG022</td>
                <td>2</td>
                <td>$120</td>
                <td>$240</td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Modal Nueva Venta -->
<div class="modal-bg" id="modalBg">
    <div class="modal">
        <h3>Nueva Venta</h3>

        <label>Nombre del producto</label>
        <input type="text" id="producto">

        <label>Categoría</label>
        <select id="categoria">
            <option>Energía</option>
            <option>Accesorios</option>
            <option>Servicios</option>
        </select>

        <label>Código</label>
        <input type="text" id="codigo">

        <label>Cantidad</label>
        <input type="number" id="cantidad" min="1" value="1">

        <label>Precio unitario</label>
        <input type="number" id="precio" min="1" value="1">

        <div class="total-box">
            Total: $<span id="total">1</span>
        </div>

        <div class="modal-buttons">
            <button class="btn btn-cancel" id="cancelar">Cancelar</button>
            <button class="btn">Guardar</button>
        </div>
    </div>
</div>

<script>
    const btnNuevaVenta = document.getElementById("btnNuevaVenta");
    const modalBg = document.getElementById("modalBg");
    const cancelar = document.getElementById("cancelar");

    const cantidad = document.getElementById("cantidad");
    const precio = document.getElementById("precio");
    const total = document.getElementById("total");

    // Abrir modal
    btnNuevaVenta.onclick = () => {
        modalBg.style.display = "flex";
    };

    // Cerrar modal
    cancelar.onclick = () => {
        modalBg.style.display = "none";
    };

    // Cálculo en tiempo real del total
    function calcularTotal() {
        total.textContent = (cantidad.value * precio.value) || 0;
    }

    cantidad.addEventListener("input", calcularTotal);
    precio.addEventListener("input", calcularTotal);
</script>

</body>
</html>
