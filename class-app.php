<?
// Импортируем доп. классы
include("class-db.php");

class app
{
    private $db;
    private $user = 0;

    // Конструктор класса базы данных:
    public function __construct()
    {
        // Пишем объект вспомогательного класса в свойство основного:
        $this->db = new db;
    }

    const siteURL = 'http://ev.lnk/'; // Константа адреса сайта
    public $shortLink;
    public $inputLink;
    public $message;

    // Просто выводим в input строку с длинной ссылкой
    function getLink()
    {
        if (isset($this->inputLink)) {
            $inputLink = $this->inputLink;
            echo $inputLink;
        }
    }

    // Создаем короткую ссылку
    function makeShortLink($inputLink)
    {
        $symQ = rand(3, 7);
        $md5 = md5($inputLink);
        $md5 = substr($md5, 0, $symQ);
        $shortLink = $md5;
        return $shortLink;
    }

    // Проверяем что текст является ссылкой
    function isUrl($text)
    {
        if (filter_var($text, FILTER_VALIDATE_URL)) {
            return $text;
        }
    }

    // Делаем проверку данных и пишем в базу
    function linkTestInsert()
    {
        if (isset($this->inputLink)) {
            $inputLink = $this->inputLink;
            if (empty($inputLink)) { // Если пустое поле то ошибка
                $this->msg('warning', 'Вы не ввели ссылку!');
            } else {
                if (strlen($inputLink) > 350) { // Если длинна введенного текса больше 350 символов, то ошибка
                    $this->msg('warning', 'Ваша ссылка слишком длинная, с такой мы не работаем ;)');
                } else {
                    if (!$this->isUrl($inputLink)) { // Проверяем URL это вообще?
                        $this->msg('error', 'Вы ввели не ссылку! Проверьте правильность ввода.');
                    } else {
                        $protectMSG  = $this->protect();
                        switch ($protectMSG) {
                            case 'BANNED':
                                $this->msg('error', 'С вашего IP поступает слишком много запросов, мы заблокировали его на сутки');
                                break;
                            case 'ERROR':
                                $this->msg('error', 'Произошла ошибка при проверке данных, попробуйте еще раз позже');
                                break;
                            case 'UNBANNED':
                                $this->msg('info', 'IP разблокирован, теперь вы можете добавить ссылку');
                                break;
                            default:
                                $shortLink = $this->makeShortLink($inputLink);
                                $datetime = date('Y-m-d H:i:s');
                                $this->db->dbconn();
                                $result = $this->db->insertLink($inputLink, $shortLink, $this->user, $datetime);
                                
                                if (!$result) {
                                    $this->msg('success', self::siteURL . 'go/' . $shortLink); // Выводим короткую ссылку 
                                } else {
                                    $shortLink = $result;
                                    $this->msg('success', self::siteURL . 'go/' . $shortLink); // Выводим короткую ссылку из базы
                                }
                                break;
                        }
                    }
                }
            }
        }
    }

    // Сообщения сервиса
    function msg($type, $text)
    {
        switch ($type) {
            case 'success':
                $this->message = "<p class='alert alert-success mt-2'>Короткая ссылка: <a id='shortLink' href='$text'>$text</a> <button type='button' class='btn btn-success ms-3' id='copyBtn'>Копировать</button></p>";
                break;
            case 'error':
                $this->message = "<p class='alert alert-danger mt-2'>$text</p>";
                break;
            case 'warning':
                $this->message = "<p class='alert alert-warning mt-2'>$text</p>";
                break;
            case 'info':
                $this->message = "<p class='alert alert-info mt-2'>$text</p>";
                break;
        }
    }


    // Роутинг
    function route()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // получаем URL
        $segments = explode('/', trim($uri, '/')); // Делим на сегменты строку адреса
        $r = $segments[0];

        switch ($r) {
            case 'go':
                $shortLink = $segments[1];
                $this->db->dbconn(); // подключаемся к БД
                $redirectURL = $this->db->getLongLink($shortLink); // получаем длинную ссылку
                if (!$redirectURL) { // Если ссылки нет в базе 
                    header('Location:' . self::siteURL . '404', true, 301); // перенеправление на 404 стр.
                } else {
                    header('Location:' . $redirectURL . '', true, 301); // само перенеправление 
                }
                break;
            case 'add':
                if (isset($_POST['inputLink'])) {
                    $this->inputLink = $_POST['inputLink'];
                    $this->linkTestInsert();
                } else {
                    $this->msg('warning', 'Вы не ввели ссылку!');
                }
                break;
            case 'terms':
                include "terms.html";
                exit();
                break;
            case 404:
                include "404.html";
                exit();
                break;
            default:
                if ($r == '') {
                } else {
                    header('Location:' . self::siteURL . '404', true, 301); // перенеправление на 404 стр.
                }
                break;
        }
    }

    // Защита от автоматического ввода
    function protect()
    {
        $enabled = 1;
        $maxAdd = 15; // Максимальное количество ссылок за указанный промежуток времени
        $minDelay = 1; // Промежуток во времени добавления в минутах (например 15 ссылок в 2 минуты)
        $banFor = 8; // Время бана в часах

        if ($enabled == 1) {
            $userIP = getHostByName(getHostName()); // это для инета $_SERVER['REMOTE_ADDR'];
            $this->db->dbconn(); // подключаемся к БД
            $checkBan = $this->db->checkBan($userIP); // Проверяем есть ли IP в базе
            if ($checkBan == FALSE) {
                $this->db->addToBanBase($userIP);
                return 'OK';
            } else {
                if ($checkBan['banned'] == 1) {
                    $nowTime = date_create(date('Y-m-d H:i:s'));
                    $banTime = date_create($checkBan['bantime']);
                    $diffTime = date_diff($banTime, $nowTime);
                    $hours = $diffTime->format('%H');
                    if ($hours >= $banFor) {
                        $this->db->unBan($userIP); // Разблокируем IP
                        $this->db->setAddedLinks($userIP, 1); // Сбрасываем счетчик ссылок
                        return 'OK'; // можно добавлять в базу ссылку
                    } else {
                        return 'BANNED'; // выводим сообщение что забанен
                    }
                } else {
                    $addLinkTimes = $checkBan['addedlinks']; // сколько ссылок уже добавлено
                    $wasTime = date_create($checkBan['lastlink']); // когда добавил последний раз
                    $nowTime = date_create(date('Y-m-d H:i:s')); // создаем дату сейчас
                    $diffTime = date_diff($wasTime, $nowTime); // вычисляем разницу в минутах
                    $minutes = $diffTime->format('%i'); // записываем ее в переменную

                    if (($addLinkTimes <= $maxAdd) && ($minutes >= $minDelay)) { // Если Последний раз добавлялись ссылки более минуты назад, то сбрасываем счетчик
                        $this->db->setAddedLinks($userIP, 0);
                    }

                    if ($addLinkTimes >= $maxAdd) { // Проверяем сколько ссылок уже добавили с этого ip

                        if ($minutes <= 1) { // и если 5 и более ссылок менее минуты назад то блокируем
                            $this->db->addToBan($userIP);
                            return 'BANNED';
                        }
                    } else {

                        $addLinkTimes++;
                        $res = $this->db->setAddedLinks($userIP, $addLinkTimes); // Увеличиваем количество добавленных ссылок
                        if ($res != FALSE) {
                            return 'OK';
                        } else {
                            return 'ERROR';
                        }
                    }
                }
            }
        } else {
            return 'OK';
        }
    }
}
