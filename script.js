document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("addClientForm");
    const submitBtn = document.getElementById("submitBtn");
    const responseMessage = document.getElementById("responseMessage");

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        let formData = new FormData(form);

        // Disable button to prevent multiple clicks
        submitBtn.disabled = true;
        submitBtn.textContent = "Adding...";

        fetch("add-client.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            responseMessage.textContent = data.message;
            responseMessage.style.color = data.status === "success" ? "green" : "red";

            if (data.status === "success") {
                setTimeout(() => {
                    window.location.href = data.redirect; // Redirect to clients.php
                }, 1500);
            } else {
                // Re-enable button in case of error
                submitBtn.disabled = false;
                submitBtn.textContent = "Add Client";
            }
        })
        .catch(error => {
            console.error("Error:", error);
            responseMessage.textContent = "An error occurred. Please try again.";
            responseMessage.style.color = "red";

            // Re-enable button
            submitBtn.disabled = false;
            submitBtn.textContent = "Add Client";
        });
    });
});


function addItem() {
    let table = document.querySelector("#invoice-items tbody");
    let rowCount = table.rows.length;
    let row = document.createElement("tr");
    
    row.innerHTML = `
        <td class='p-2 border'><input type='text' name='items[${rowCount}][description]' required class='w-full p-2 border rounded'></td>
        <td class='p-2 border'><input type='number' name='items[${rowCount}][quantity]' class='quantity w-full p-2 border rounded' oninput='calculateRow(this)' required></td>
        <td class='p-2 border'><input type='number' step='0.01' name='items[${rowCount}][unit_price]' class='unit_price w-full p-2 border rounded' oninput='calculateRow(this)' required></td>
        <td class='p-2 border'><input type='number' step='0.01' name='items[${rowCount}][total_price]' class='total_price w-full p-2 border rounded' readonly></td>
        <td class='p-2 border'><button type='button' class='bg-red-600 text-white px-3 py-1 rounded' onclick='removeRow(this)'>Remove</button></td>
    `;
    
    table.appendChild(row);
}

function removeRow(button) {
    button.closest("tr").remove();
    calculateTotal();
}

function calculateRow(input) {
    let row = input.closest("tr");
    let qty = row.querySelector('.quantity').value;
    let price = row.querySelector('.unit_price').value;
    row.querySelector('.total_price').value = (qty * price).toFixed(2);
    calculateTotal();
}

function calculateTotal() {
    let total = 0;
    document.querySelectorAll('.total_price').forEach(input => total += parseFloat(input.value) || 0);
    document.getElementById('subtotal').value = total.toFixed(2);
    let tax = parseFloat(document.getElementById('tax').value) || 0;
    document.getElementById('total').value = (total + (total * tax / 100)).toFixed(2);
}
