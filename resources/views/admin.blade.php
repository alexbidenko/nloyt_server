<script>
let users = [];
let orders = [];
let userDevices = [];

const getData = () => {
    fetch('http://194.182.85.89/api/check/users').then(r => r.json()).then(data => users = data);
    fetch('http://194.182.85.89/api/check/orders').then(r => r.json()).then(data => orders = data);
    fetch('http://194.182.85.89/api/check/user_devices').then(r => r.json()).then(data => userDevices = data);
};
getData();

async function createOrders() {
    if(!users || !orders || !userDevices) return;

    let count = +document.getElementById('countOrders').value;
    for (const user of users) {
        const devices = userDevices.filter(device => device.ownerId === user.id);

        for (const device of devices) {
            if(!orders.find(order => order.devicePin === device.pin && [1, 2].indexOf(order.status))) {
                const r = await fetch('http://194.182.85.89/api/user/order/' + device.pin, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': user.token
                    },
                    body: JSON.stringify({
                        serviceId: 1,
                        type: 't',
                        services_ids: '1'
                    })
                });
                const data = await r.json();
                if(data.success === true) {
                    count--;
                    if(count <= 0) {
                        alert('Заявки созданы');
                    }
                }
            }
        }
    }
}
</script>

<label>
    Колличество заявок для создания
    <input type="number" id="countOrders">
</label>
<button onclick="createOrders()">Создать</button>
