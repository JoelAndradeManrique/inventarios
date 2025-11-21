<?php
require_once '../config/db.php';
require_once '../components/header.php';

if ($rol !== 'ADMIN') {
    echo "<script>window.location.href='panel.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; background: #f5f6fa; }
        .content { padding: 40px; max-width: 1200px; margin: 0 auto; }
        .section-title { font-size: 28px; margin-bottom: 25px; color: #333; }

        /* FILTROS */
        .filters { display: flex; gap: 15px; margin-bottom: 25px; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); align-items: end; flex-wrap: wrap;}
        .filter-group { display: flex; flex-direction: column; }
        .filter-group label { font-size: 12px; font-weight: bold; margin-bottom: 5px; color: #666; }
        .filters input, .filters select { padding: 10px; border: 1px solid #ccc; border-radius: 8px; font-size: 14px; min-width: 150px; }
        .btn-search { background: #f7d44c; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: bold; height: 38px; }
        .btn-download { background: #2c3e50; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; margin-left: auto; font-weight: bold; height: 38px;}

        /* TABLA */
        table { width: 100%; border-collapse: collapse; margin-top: 25px; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        table thead { background: #1d1f2f; color: white; }
        table th, table td { padding: 14px; text-align: left; border-bottom: 1px solid #e5e5e5; }
        
        /* DETALLE DE CORTE (LISTA HORIZONTAL) */
        .row-detalle { display: none; background-color: #fafafa; }
        .row-detalle.visible { display: table-row; }
        
        /* Estilo de las "Pastillas" o Tags de productos */
        .product-list {
                display: block !important; /* Desactiva el modo horizontal (flex) */
            }
        .prod-pill {
            background: white;
            border: 1px solid #ddd;
            color: #555;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .prod-pill b { margin-left: 5px; color: #333; }

        .toggle-icon { cursor: pointer; font-weight: bold; color: #3498db; width: 20px; text-align: center; }

        /* BADGES */
        .badge { padding: 5px 10px; border-radius: 15px; font-size: 12px; font-weight: bold; }
        .badge-entrada { background: #d4edda; color: #155724; }
        .badge-salida { background: #f8d7da; color: #721c24; }

        /* HEADER DE IMPRESIÓN (Oculto normalmente) */
        #printHeader { display: none; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }

        .prod-row {
                display: block !important;
                width: 100% !important;
                border-bottom: 1px solid #000 !important; /* Línea divisoria */
                padding: 5px 0 !important;
                margin: 0 !important;
            }

        /* Quitar la línea del último elemento */
        .prod-row:last-child { 
            border-bottom: none; 
        }
        /* =========================================
           ESTILOS DE IMPRESIÓN (PDF)
           ========================================= */
       @media print {
            @page { margin: 15mm; } 
            
            /* 1. Ocultar elementos de navegación y botones */
            .header, #headerContainer, .filters, .btn-download { display: none !important; }
            
            /* 2. Mostrar Header Personalizado con fecha */
            #printHeader { display: block !important; }
            
            /* 3. Ajustes generales de hoja */
            body { background: white; -webkit-print-color-adjust: exact; }
            .content { padding: 0; width: 100%; }
            
            /* 4. Tabla limpia */
            table { box-shadow: none; width: 100%; border: 1px solid #ccc; }
            th { background: #eee !important; color: black !important; border: 1px solid #ccc; }
            td { border: 1px solid #ccc; }

            /* 5. DETALLES VISIBLES Y VERTICALES */
            .row-detalle { display: table-row !important; }
            
            /* >>> AQUÍ QUITAMOS EL EMOJI <<< */
            .toggle-icon { display: none !important; } /* Oculta la celda del + */
            th:first-child { display: none !important; } /* Oculta el encabezado vacío de esa columna */
            
            /* Estilos de la lista vertical */
            .product-list { 
                background: none !important; 
                display: block !important; /* Fuerza vertical */
            }
            
            .prod-row {
                border-bottom: 1px solid #000 !important; 
                color: black !important;
                padding: 5px 10px !important;
                width: 100% !important;
                display: block !important;
                margin: 0 !important;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <main class="content">
        
        <div id="printHeader">
            <h2 style="margin:0;">Reporte de Cortes y Movimientos</h2>
            <p style="margin:5px 0 0 0; color:#666;">Generado el: <span id="fechaImpresion"></span></p>
        </div>

        <h2 class="section-title">Reportes y Estadísticas</h2>

        <div class="filters">
            <div class="filter-group">
                <label>Tipo de Reporte</label>
                <select id="tipoReporte">
                    <option value="CORTES">💰 Cortes de Caja</option>
                    <option value="MOVIMIENTOS">📦 Kardex (Entradas/Salidas)</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Filtro Rápido</label>
                <select id="filtroRapido">
                    <option value="hoy">Hoy</option>
                    <option value="semana">Esta Semana</option>
                    <option value="mes">Este Mes</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Desde</label>
                <input type="date" id="fechaInicio">
            </div>
            <div class="filter-group">
                <label>Hasta</label>
                <input type="date" id="fechaFin">
            </div>
            <button class="btn-search" onclick="generarReporte()">Buscar</button>
            <button class="btn-download" onclick="imprimirReporte()">🖨️ Imprimir PDF</button>
        </div>

        <table id="tablaReportes">
            <thead id="theadReportes"></thead>
            <tbody id="tbodyReportes"></tbody>
        </table>
    </main>

    <script>
        // Configuración de Fechas
        const inpInicio = document.getElementById('fechaInicio');
        const inpFin = document.getElementById('fechaFin');
        const selRapido = document.getElementById('filtroRapido');

        function setFechas(tipo) {
            const hoy = new Date();
            const yyyy = hoy.getFullYear();
            const mm = String(hoy.getMonth() + 1).padStart(2, '0');
            const dd = String(hoy.getDate()).padStart(2, '0');
            const hoyStr = `${yyyy}-${mm}-${dd}`;

            if (tipo === 'hoy') {
                inpInicio.value = hoyStr;
                inpFin.value = hoyStr;
            } else if (tipo === 'semana') {
                const day = hoy.getDay() || 7;
                if(day !== 1) hoy.setHours(-24 * (day - 1)); 
                inpInicio.value = hoy.toISOString().split('T')[0];
                inpFin.value = hoyStr;
            } else if (tipo === 'mes') {
                inpInicio.value = `${yyyy}-${mm}-01`;
                inpFin.value = hoyStr;
            }
        }

        selRapido.addEventListener('change', (e) => {
            setFechas(e.target.value);
            generarReporte();
        });

        setFechas('hoy');
        document.addEventListener('DOMContentLoaded', generarReporte);

        // Función especial para imprimir
        function imprimirReporte() {
            // Poner fecha actual en el header de impresión
            const ahora = new Date();
            document.getElementById('fechaImpresion').textContent = 
                ahora.toLocaleDateString() + ' ' + ahora.toLocaleTimeString();
            window.print();
        }

        // --- GENERAR REPORTE ---
        async function generarReporte() {
            const tipo = document.getElementById('tipoReporte').value;
            const inicio = inpInicio.value;
            const fin = inpFin.value;
            const tbody = document.getElementById('tbodyReportes');
            const thead = document.getElementById('theadReportes');

            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Cargando...</td></tr>';

            try {
                const res = await fetch(`../api/reportes.php?tipo=${tipo}&desde=${inicio}&hasta=${fin}`);
                const datos = await res.json();

                tbody.innerHTML = '';
                if (datos.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">Sin datos.</td></tr>';
                    return;
                }

                if (tipo === 'CORTES') {
                    thead.innerHTML = `
                        <tr>
                            <th></th>
                            <th>Fecha Corte</th>
                            <th>Cajero</th>
                            <th>Ventas</th>
                            <th>Ganancia</th>
                        </tr>
                    `;
                    datos.forEach(d => {
                        // 1. Fila Principal
                        const trMain = document.createElement('tr');
                        trMain.innerHTML = `
                            <td class="toggle-icon" onclick="toggleDetalle(this)">➕</td>
                            <td>${d.fecha_corte}</td>
                            <td>${d.usuario}</td>
                            <td><strong>$${d.ventas_totales}</strong></td>
                            <td style="color:green; font-weight:bold;">$${d.ganancias_totales}</td>
                        `;
                        
                        // 2. Procesar Lista de Productos (Horizontal)
                        // El backend nos manda "Coca (x1)||Papas (x2)" gracias al separador ||
                        let badgesHtml = '<div style="color:#999; font-style:italic; padding:10px;">Sin productos</div>';
                        
                        if (d.detalle_productos) {
                            const lista = d.detalle_productos.split('||');
                            
                            // Mapeamos a DIVs que actúan como filas de tabla
                            badgesHtml = lista.map(prod => {
                                // Quitamos el emoji del carrito para que se vea más formal al imprimir
                                return `<div class="prod-row">• ${prod}</div>`;
                            }).join(''); 
                        }

                        // 3. Fila de Detalle
                        const trDetalle = document.createElement('tr');
                        trDetalle.className = 'row-detalle'; 
                        trDetalle.innerHTML = `
                            <td colspan="5" style="padding:0;">
                                <div class="product-list">
                                    ${badgesHtml}
                                </div>
                            </td>
                        `;

                        tbody.appendChild(trMain);
                        tbody.appendChild(trDetalle);
                    });

                } else {
                    // MOVIMIENTOS (KARDEX)
                    thead.innerHTML = `<tr><th>Fecha</th><th>Tipo</th><th>Producto</th><th>Cant.</th><th>Total</th><th>Usuario</th></tr>`;
                    datos.forEach(d => {
                        const esEntrada = d.tipo_movimiento === 'ENTRADA';
                        const badge = esEntrada ? 'badge-entrada' : 'badge-salida';
                        const precio = esEntrada ? d.precio_compra_momento : d.precio_venta_momento;
                        const total = (precio * d.cantidad).toFixed(2);

                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${d.fecha_movimiento}</td>
                            <td><span class="badge ${badge}">${d.tipo_movimiento}</span></td>
                            <td>${d.producto} <small style="color:#999">(${d.codigo_sku})</small></td>
                            <td>${d.cantidad}</td>
                            <td>$${total}</td>
                            <td>${d.usuario}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                }

            } catch (error) { console.error(error); }
        }

        window.toggleDetalle = function(icon) {
            const filaDetalle = icon.parentElement.nextElementSibling;
            filaDetalle.classList.toggle('visible');
            icon.textContent = filaDetalle.classList.contains('visible') ? '➖' : '➕';
        };
    </script>

</body>
</html>