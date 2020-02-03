<?php

namespace BlaubandEmailInbox\Components;

use BlaubandEmail\Models\LoggedMail;

class InboxComponent
{

    private $connection;

    public function __construct($username, $password, $type, $host, $port, $folder, $ssl)
    {
        $this->connection = $this->inbox_login($type, $host, $port, $username, $password, $folder, $ssl);
    }

    public function isConnected()
    {
        return ($this->connection);
    }

    public function getSortedHeaders($page = 1, $per_page = 25, $sort = ['desc', 'date'])
    {
        $limit = ($per_page * $page);
        $start = ($limit - $per_page) + 1;
        $start = ($start < 1) ? 1 : $start;
        $limit = (($limit - $start) != ($per_page - 1)) ? ($start + ($per_page - 1)) : $limit;
        $info = imap_check($this->connection);
        $limit = ($info->Nmsgs < $limit) ? $info->Nmsgs : $limit;

        if (true === is_array($sort)) {
            $sorting = array(
                'direction' => array(
                    'asc' => 0,
                    'desc' => 1
                ),

                'by' => array(
                    'date' => SORTDATE,
                    'arrival' => SORTARRIVAL,
                    'from' => SORTFROM,
                    'subject' => SORTSUBJECT,
                    'size' => SORTSIZE
                ));
            $by = (true === is_int($by = $sorting['by'][$sort[0]]))
                ? $by
                : $sorting['by']['date'];
            $direction = (true === is_int($direction = $sorting['direction'][$sort[1]]))
                ? $direction
                : $sorting['direction']['desc'];

            $sorted = imap_sort($this->connection, $by, $direction);

            $msgs = array_chunk($sorted, $per_page);
            $msgs = $msgs[$page - 1];
        } else {
            $msgs = range($start, $limit);
        }

        $result = imap_fetch_overview($this->connection, implode($msgs, ','), 0);
        if (false === is_array($result)) return false;

        if (true === is_array($sorted)) {
            $tmp_result = array();
            foreach ($result as $r)
                $tmp_result[$r->msgno] = $r;

            $result = array();
            foreach ($msgs as $msgno) {
                $result[] = $tmp_result[$msgno];
            }
        }

        $return = array('res' => $result,
            'start' => $start,
            'limit' => $limit,
            'sorting' => array('by' => $sort[0], 'direction' => $sort[1]),
            'total' => imap_num_msg($this->connection));
        $return['pages'] = ceil($return['total'] / $per_page);
        return $return;
    }

    public function getBody($header)
    {
        $structure = imap_fetchstructure($this->connection, $header->msgno);

        //text
        if ($structure->type === 0) {
            return nl2br(imap_fetchbody($this->connection, $header->msgno, '1'));
        }

        //multipart
        if ($structure->type === 1) {
            return quoted_printable_decode(imap_fetchbody($this->connection, $header->msgno, '1.2'));
        }

        return '';
    }

    public function getParts($header)
    {
        //https://www.php.net/manual/de/function.imap-fetchstructure.php#54438
        $structure = imap_fetchstructure($this->connection, $header->msgno);
        $partsArray = [];

        if (count($structure->parts) > 0) {
            foreach ($structure->parts as $index => $part) {
                $p = $this->parsePart($header->msgno, $index + 1, $part);
                if (array_key_exists('text', $p)) $partsArray['text'] = $p['text'];
                if (array_key_exists('attachment', $p)) $partsArray['attachment'][] = $p['attachment'];
            }
        } else {
            $text = imap_body($this->connection, $header->msgno);
            if ($structure->encoding === 4) $text = quoted_printable_decode($text);
            if (strtoupper($structure->subtype) === 'PLAIN') $partsArray['text'] = nl2br($text);
            if (strtoupper($structure->subtype) === 'HTML') $partsArray['text'] = $text;
        }

        return $partsArray;
    }

    private function parsePart($msgno, $index, $p)
    {
        $part = imap_fetchbody($this->connection, $msgno, $index);
        $result = [];

        if ($p->type != 0) {

            if ($p->encoding == 3) $part = base64_decode($part);
            if ($p->encoding == 4) $part = quoted_printable_decode($part);

            $filename = '';
            if (count($p->dparameters) > 0) {
                foreach ($p->dparameters as $dparam) {
                    if (
                        (strtoupper($dparam->attribute) === 'NAME') ||
                        (strtoupper($dparam->attribute) === 'FILENAME')
                    ) $filename = $dparam->value;
                }
            }

            if ($filename == '') {
                if (count($p->parameters) > 0) {
                    foreach ($p->parameters as $param) {
                        if (
                            (strtoupper($param->attribute) === 'NAME') ||
                            (strtoupper($param->attribute) === 'FILENAME')
                        ) $filename = $param->value;
                    }
                }
            }

            if ($filename != '') {
                $result['attachment'] = array('filename' => mb_decode_mimeheader($filename));  //, 'binary' => $part);
            }

        }

        if ($p->type == 0) {
            if ($p->encoding == 4) $part = quoted_printable_decode($part);
            if ($p->encoding == 3) $part = base64_decode($part);

            if (strtoupper($p->subtype) === 'PLAIN') $result['text'] = nl2br($part);
            if (strtoupper($p->subtype) === 'HTML') $result['text'] = $part;
        }

        if (count($p->parts) > 0) {
            foreach ($p->parts as $pno => $parr) {
                $result = $this->parsepart($msgno, ($index . '.' . ($pno + 1)), $parr);
            }
        }

        return $result;
    }


    private function inbox_login($type, $host, $port, $user, $pass, $folder, $ssl)
    {
        $ssl = ($ssl === false) ? 'novalidate-cert' : 'ssl';
        $type = ($type === 'pop3') ? $type : 'imap';

        return imap_open('{' . "$host:$port/$type/$ssl" . "}$folder", $user, $pass);
    }

    private function inbox_stat()
    {
        $check = imap_mailboxmsginfo($this->connection);
        return ((array)$check);
    }

    private function inbox_list($message = '')
    {
        if ($message) {
            $range = $message;
        } else {
            $MC = imap_check($this->connection);
            $range = '1:' . $MC->Nmsgs;
        }

        $response = imap_fetch_overview($this->connection, $range);
        foreach ($response as $msg) $result[$msg->msgno] = (array)$msg;

        return $result;
    }

    private function inbox_retr($message)
    {
        return (imap_fetchheader($this->connection, $message, FT_PREFETCHTEXT));
    }

    private function inbox_dele($message)
    {
        return (imap_delete($this->connection, $message));
    }

    private function mail_parse_headers($headers)
    {
        $headers = preg_replace('/\r\n\s+/m', '', $headers);
        preg_match_all('/([^: ]+): (.+?(?:\r\n\s(?:.+?))*)?\r\n/m', $headers, $matches);
        foreach ($matches[1] as $key => $value) $result[$value] = $matches[2][$key];
        return ($result);
    }

    private function mail_mime_to_array($mid, $parse_headers = false)
    {
        $mail = imap_fetchstructure($this->connection, $mid);
        $mail = $this->mail_get_parts($mid, $mail, 0);
        if ($parse_headers) $mail[0]['parsed'] = $this->mail_parse_headers($mail[0]['data']);
        return ($mail);
    }

    private function mail_get_parts($mid, $part, $prefix)
    {
        $attachments = array();
        $attachments[$prefix] = $this->mail_decode_part($mid, $part, $prefix);

        if (isset($part->parts)) // multipart
        {
            $prefix = ($prefix == '0') ? '' : "$prefix.";
            foreach ($part->parts as $number => $subpart)
                $attachments = array_merge($attachments, $this->mail_get_parts($imap, $mid, $subpart, $prefix . ($number + 1)));
        }
        return $attachments;
    }

    private function mail_decode_part($message_number, $part, $prefix)
    {
        $attachment = array();

        if ($part->ifdparameters) {
            foreach ($part->dparameters as $object) {
                $attachment[strtolower($object->attribute)] = $object->value;
                if (strtolower($object->attribute) == 'filename') {
                    $attachment['is_attachment'] = true;
                    $attachment['filename'] = $object->value;
                }
            }
        }

        if ($part->ifparameters) {
            foreach ($part->parameters as $object) {
                $attachment[strtolower($object->attribute)] = $object->value;
                if (strtolower($object->attribute) == 'name') {
                    $attachment['is_attachment'] = true;
                    $attachment['name'] = $object->value;
                }
            }
        }

        $attachment['data'] = imap_fetchbody($this->connection, $message_number, $prefix);
        if ($part->encoding == 3) { // 3 = BASE64
            $attachment['data'] = base64_decode($attachment['data']);
        } elseif ($part->encoding == 4) { // 4 = QUOTED-PRINTABLE
            $attachment['data'] = quoted_printable_decode($attachment['data']);
        }
        return ($attachment);
    }
}