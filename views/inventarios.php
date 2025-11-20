<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inventarios</title>

    <style>
        body {
            background: #f4f4f4;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        /* ================= HEADER ================= */
        .header {
            background: #1e1e2f;
            color: white;
            padding: 12px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }

        .left-section {
            display: flex;
            gap: 35px;
            align-items: center;
        }

        .logo {
            font-size: 22px;
            font-weight: bold;
            color: #f5d04c;
            text-decoration: none;
        }

        .nav {
            display: flex;
            gap: 20px;
        }

        .nav a {
            text-decoration: none;
            color: #ddd;
            padding: 8px 12px;
            border-radius: 6px;
        }

        .nav a:hover {
            background: rgba(255,255,255,0.15);
        }

        .nav .active {
            background: rgba(255,255,255,0.25);
            color: white;
        }

        /* User menu */
        .user-menu {
            position: relative;
        }

        .user-initials {
            width: 42px;
            height: 42px;
            background: #f5d04c;
            color: #1e1e2f;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            cursor: pointer;
        }

        .dropdown {
            position: absolute;
            right: 0;
            margin-top: 8px;
            background: white;
            width: 160px;
            border-radius: 8px;
            display: none;
            box-shadow: 0 4px 14px rgba(0,0,0,0.25);
        }

        .dropdown.show {
            display: block;
        }

        .dropdown a {
            display: block;
            padding: 10px;
            color: #333;
            text-decoration: none;
        }

        .dropdown a:hover {
            background: #f5d04c;
            color: black;
        }

        /* ================= CONTENIDO ================= */
        .container {
            width: 95%;
            margin: 25px auto;
        }

        h2 {
            color: #444;
        }

        .btn {
            background: #f5d04c;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn:hover {
            background: #e2be43;
        }

        /* TABLA */
        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            margin-top: 15px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.09);
        }

        th {
            background: #1e1e2f;
            color: white;
            padding: 12px;
            text-align: left;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
        }

        tr:hover {
            background: #f5f5f5;
        }

        /* Fila expandida */
        .expand-row {
            display: none;
            background: #fafafa;
            padding: 15px;
            border-left: 4px solid #f5d04c;
        }

        .expand-row.visible {
            display: table-row;
        }

        /* ================= MODAL ================= */
        .modal-bg {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            display: none;
            justify-content: center;
            align-items: center;
        }

        .modal {
            width: 420px;
            background: white;
            padding: 25px;
            border-radius: 12px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 12px;
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

    <!-- ================= HEADER ================= -->
    <header class="header">
        <div class="left-section">
            <a href="#" class="logo">Inventarios</a>

            <nav class="nav">
                <a href="movimientos.html">Movimientos</a>
                <a href="#" class="active">Gestión de inventarios</a>
                <a href="reportes.html">Reportes</a>
            </nav>
        </div>

        <div class="user-menu">
            <div id="userInitials" class="user-initials">JA</div>
            <div id="dropdown" class="dropdown">
                <a href="#">Editar usuario</a>
                <a href="#">Cerrar sesión</a>
            </div>
        </div>
    </header>

    <!-- ================= CONTENIDO ================= -->
    <div class="container">
        <h2>Gestión de Inventarios</h2>

        <button class="btn" id="btnAdd">Añadir producto</button>
        <input type="text" id="searchBar" placeholder="Buscar por nombre o código..." 
       style="margin-top:15px; padding:10px; width:250px; border-radius:6px; border:1px solid #ccc;">


        <table id="tablaProductos">

            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Precio Unitario</th>
                    <th>Precio Compra</th>
                    <th>Precio Venta</th>
                    <th>Stock</th>
                </tr>
            </thead>

            <tbody>
                <!-- Ejemplo 1 -->
                <tr class="producto">
                    <td>Gas LP</td>
                    <td>Energía</td>
                    <td>$15</td>
                    <td>$10</td>
                    <td>$15</td>
                    <td>250</td>
                </tr>
                <tr class="expand-row">
                    <td colspan="6">
                        <strong>Detalles del producto:</strong><br>
                        Código: GLP001 <br>
                        Última compra: $1000 <br>
                        Fecha ingreso: 01/10/2025
                    </td>
                </tr>

                <!-- Ejemplo 2 -->
                <tr class="producto">
                    <td>Regulador</td>
                    <td>Accesorios</td>
                    <td>$120</td>
                    <td>$90</td>
                    <td>$120</td>
                    <td>40</td>
                </tr>
                <tr class="expand-row">
                    <td colspan="6">
                        <strong>Detalles del producto:</strong><br>
                        Código: REG022 <br>
                        Última compra: $1800 <br>
                        Fecha ingreso: 03/11/2025
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- ================= MODAL ================= -->
    <div class="modal-bg" id="modalBg">
        <div class="modal">
            <h3>Añadir producto</h3>

            <label>Categoría</label>
            <select id="categoria">
                <option>Energía</option>
                <option>Accesorios</option>
                <option>Servicios</option>
            </select>

            <label>Código</label>
            <input type="text" id="codigo">

            <label>Nombre del producto</label>
            <input type="text" id="nombre">

            <label>Cantidad en stock (ingreso)</label>
            <input type="number" id="cantidad" value="1">

            <label>Precio unitario de compra</label>
            <input type="number" id="precio" value="1">

            <div class="total-box">
                Total compra: $<span id="total">1</span>
            </div>

            <div class="modal-buttons">
                <button class="btn btn-cancel" id="cancelar">Cancelar</button>
                <button class="btn">Guardar</button>
            </div>
        </div>
    </div>

    <script>
        /* ==== Dropdown ==== */
        const userInitials = document.getElementById("userInitials");
        const dropdown = document.getElementById("dropdown");

        userInitials.onclick = () => dropdown.classList.toggle("show");

        window.onclick = (e) => {
            if (!userInitials.contains(e.target)) dropdown.classList.remove("show");
        };

        /* ==== Modal ==== */
        const btnAdd = document.getElementById("btnAdd");
        const modalBg = document.getElementById("modalBg");
        const cancelar = document.getElementById("cancelar");

        btnAdd.onclick = () => modalBg.style.display = "flex";
        cancelar.onclick = () => modalBg.style.display = "none";

        /* ==== Calcular total ==== */
        const cantidad = document.getElementById("cantidad");
        const precio = document.getElementById("precio");
        const total = document.getElementById("total");

        function calcTotal() {
            total.textContent = cantidad.value * precio.value;
        }

        cantidad.oninput = precio.oninput = calcTotal;

        /* ==== Expandir filas ==== */
        const filas = document.querySelectorAll(".producto");

        filas.forEach((row, index) => {
            row.addEventListener("click", () => {
                const expand = row.nextElementSibling;
                expand.classList.toggle("visible");
                
            });
            
        });
        const searchBar = document.getElementById("searchBar");
const filasProductos = document.querySelectorAll("#tablaProductos tbody tr");

searchBar.addEventListener("input", () => {
    const texto = searchBar.value.toLowerCase();

    filasProductos.forEach((fila, index) => {
        // Solo hacemos match en las filas que son productos, no en las expand-row
        if (fila.classList.contains("producto")) {
            const nombre = fila.children[0].textContent.toLowerCase();
            const categoria = fila.children[1].textContent.toLowerCase();
            const codigo = fila.nextElementSibling.textContent.toLowerCase();

            // Criterio de búsqueda
            const coincide =
                nombre.includes(texto) ||
                categoria.includes(texto) ||
                codigo.includes(texto);

            // Mostrar u ocultar
            fila.style.display = coincide ? "" : "none";
            fila.nextElementSibling.style.display = coincide ? "" : "none";
        }
    });
});
    </script>

</body>
</html>
