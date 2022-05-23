<?
require_once("class-app.php");
$app = new app;
$app->route();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Сокращатель ссылок</title>
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="icon" type="image/x-icon" href="/img/link.png">
</head>

<body>

    <div class="px-4 py-5 my-5 text-center">
        <a href="/"><img class="mx-auto mb-4" src="/img/link.png" alt="" width="72"></a>
        <h1 class="display-5 fw-bold">Сокращатель</h1>
        <div class="col-lg-6 mx-auto">
            <p class="lead mb-4">Легко и быстро сделать ссылку короткой при помощи нашего сервиса</p>
            <div class="d-grid d-sm-flex justify-content-sm-center">
                <form action="/add/" method="post">
                    <div class="col-auto">
                        <input value="<? $app->getLink(); ?>" size="40" type="text" id="inputLink" name="inputLink" class="form-control-lg border-0" placeholder="Введите длинную ссылку" required>
                        <? echo $app->message; ?>
                    </div>
                    <div class="col-auto mt-4">
                        <button type="submit" class="btn btn-primary btn-lg px-4 gap-3">Сократить</button>
                        <!-- <button type="button" class="btn btn-outline-secondary btn-lg px-4">Регистрация</button> -->
                    </div>
                </form>

            </div>
        </div>
    </div>

    <footer>
        <p class="text-center mb-1"><a href="/terms" title="условия сервиса">Условия использования сервиса</a></p>
        <p class="text-center"><a target="blank" href="https://www.flaticon.com/ru/icons" title="иконка ссылки">Иконка ссылки от Freepik - Flaticon</a></p>
    </footer>
 
    <script src="/js/main.js"></script>   
</body>
</html>