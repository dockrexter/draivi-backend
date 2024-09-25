<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alko Products</title>
</head>
<body>
    <button id="listButton">List</button>
    <button id="emptyButton">Empty</button>
    <div id="tableContainer"></div>

    <script>
        document.getElementById('listButton').addEventListener('click', function() {
            fetch('fetch_data.php')
                .then(response => response.json())
                .then(data => {
                    let tableHTML = '<table border="1"><tr><th>Number</th><th>Name</th><th>Bottle Size</th><th>Price</th><th>Price GBP</th><th>Order Amount</th><th>Actions</th></tr>';
                    data.forEach(product => {
                        tableHTML += `<tr>
                            <td>${product.number}</td>
                            <td>${product.name}</td>
                            <td>${product.bottlesize}</td>
                            <td>${product.price}</td>
                            <td>${product.priceGBP}</td>
                            <td id="orderAmount-${product.number}">${product.orderamount}</td>
                            <td>
                                <button onclick="updateOrderAmount(${product.number}, 1)">Add</button>
                                <button onclick="updateOrderAmount(${product.number}, 0)">Clear</button>
                            </td>
                        </tr>`;
                    });
                    tableHTML += '</table>';
                    document.getElementById('tableContainer').innerHTML = tableHTML;
                })
                .catch(error => {
                    console.error('There was a problem with the fetch operation:', error);
                });
        });

        document.getElementById('emptyButton').addEventListener('click', function() {
            document.getElementById('tableContainer').innerHTML = '';
        });

        function updateOrderAmount(productNumber, action) {
            fetch('update_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ number: productNumber, action: action })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById(`orderAmount-${productNumber}`).innerText = data.orderamount;
                } else {
                    console.error('Error updating order amount:', data.message);
                }
            });
        }
    </script>
</body>
</html>
