<!DOCTYPE html>
<html lang="ru">
<head>
    <title>Тестирование девайсов</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

    <style type="text/css">
    </style>
    <script type="text/javascript">
        const sendMessage = () => {
            const deviceAddress = document.getElementById('deviceAddress').value;
            const deviceMessage = document.getElementById('deviceMessage').value;

            if (!deviceAddress || !deviceMessage) return;

            fetch('/api/device/send_message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    address: deviceAddress,
                    message: deviceMessage,
                }),
            }).then(r => r.json()).then(body => {
                if (body.success) {
                    alert('Сообщение успешно отправлено!');
                } else {
                    console.error(body);
                }
            });
        };
    </script>
</head>
<body>

<div class="container py-5">
    <div class="row">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="form-group">
                <label for="deviceAddress">Имя очереди</label>
                <input type="text" class="form-control" id="deviceAddress" placeholder="Введите имя очереди">
            </div>
            <div class="form-group">
                <label for="deviceMessage">Текст сообщения</label>
                <textarea class="form-control" id="deviceMessage" rows="5"></textarea>
            </div>
            <button type="button" class="btn btn-primary" onclick="sendMessage()">Отправить</button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>

</body>
</html>
