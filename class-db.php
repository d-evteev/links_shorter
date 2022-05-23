<?
class db
{
    const dbname = 'evlnk';
    const user = 'root';
    const pass = '';
    private $dbh;

    // Соединяемся с базой
    function dbconn()
    {
        $this->dbh = new PDO('mysql:host=localhost;dbname=' . self::dbname, self::user, self::pass);
    }


    // Проверяем есть ли значение в БД
    function checkLink($colName, $link)
    {
        $sql = "SELECT * FROM url WHERE $colName = ?";
        $sth = $this->dbh->prepare($sql);
        $sth->execute(array($link));
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        if (!empty($result[$colName])) {
            return $result;
        } else {
            return FALSE;
        }
    }

    // Функция добавления ссылки в базу
    function insertLink($inputLink, $shortLink, $user, $date_time)
    {
        $resLink = $this->checkLink('input_link', $inputLink);
        $resLinkSh = $this->checkLink('short_link', $shortLink);
        if ($resLink != FALSE) {
            return ($resLink['short_link']);
        } else {
            if ($resLinkSh != FALSE) {
                $shortLink = $shortLink . rand(654, 15255); // Если вдруг уже есть такая короткая ссылка
            }
            $sql = 'insert into url (input_link, short_link, user_id, date_time) values (?, ?, ?, ?)';
            $sth = $this->dbh->prepare($sql);
            $sth->execute(array($inputLink, $shortLink, $user, $date_time));
        }
    }

    // Получаем ссылку из базы
    function getLongLink($shortLink)
    {
        $sth = $this->dbh->query("
                SELECT input_link, short_link FROM url WHERE short_link = '$shortLink'
            ", PDO::FETCH_ASSOC);

        $row = $sth->fetch();
        if ($row != FALSE) {
            return ($row['input_link']);
        } else {
            return FALSE;
        }
    }

    // Проверяем есть IP в списке на блокировку 
    function checkBan($ip)
    {
        $sql = "SELECT * FROM ban WHERE ip = ?";
        $sth = $this->dbh->prepare($sql);
        $sth->execute(array($ip));
        $result = $sth->fetch(PDO::FETCH_ASSOC);

        if (!$sth) { // Если адреса нет в базе
            return FALSE;
        } else {
            return $result;
        }
    }

    // Добавляем в базу бана
    function addToBanBase($ip)
    {
        $lastlink = date('Y-m-d H:i:s');
        $sql = 'INSERT INTO ban (ip, lastlink, addedlinks, banned, bantime) VALUES (?, ?, ?, ?, ?)';
        $sth = $this->dbh->prepare($sql);
        $sth->execute(array($ip, $lastlink, 1, 0, ''));
        if (!$sth) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    // Устанавливаем последнее время добавления ссылки и количество
    function setAddedLinks($ip, $count)
    {   
        $nowTime = date('Y-m-d H:i:s');
        $sql = 'UPDATE ban SET addedlinks = ?, lastlink = ? WHERE ip = ?';
        $sth = $this->dbh->prepare($sql);
        $sth->execute(array($count, $nowTime, $ip));
        if (!$sth) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    // Блокируем по IP
    function addToBan($ip)
    {
        $bantime = date('Y-m-d H:i:s');
        $sql = 'UPDATE ban SET banned = 1, bantime = ? WHERE ip = ?';
        $sth = $this->dbh->prepare($sql);
        $sth->execute(array($bantime, $ip));
        return $sth;
    }

    // Разблокируем IP
    function unBan($ip) 
    {
        $sql = 'UPDATE ban SET banned = 0, addedlinks = 0 WHERE ip = ?';
        $sth = $this->dbh->prepare($sql);
        $sth->execute(array($ip));
        return $sth;
    }
}
