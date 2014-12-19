<?php
/**
 * This file is part of the Crystal package.
 *
 * (c) Sergey Novikov (novikov.stranger@gmail.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 09.12.2014
 * Time: 1:28
 */

namespace core\session_drivers;

use core\App;
use core\Config;
use core\generic\DbDriver;
use core\generic\Session;
use core\Request;

/**
 * Class DbSession
 * @package core\session_drivers
 *
 * SQL-instruction for table creating:
 *
 * CREATE TABLE sessions (
 *   id VARCHAR(32) NOT NULL,
 *   created DATETIME NOT NULL,
 *   updated DATETIME NOT NULL,
 *   data text,
 *   ip_address text,
 *   user_agent text,
 *   PRIMARY KEY (id)
 * )
 */
class DbSession extends Session
{
    /**
     * @var DbDriver
     */
    protected $db;

    /**
     * Table name for storing of sessions
     * @var string
     */
    protected $table_name = 'sessions';

    /*===============================================================*/
    /*                        M E T H O D S                          */
    /*===============================================================*/

    public function __construct(Request $request, DbDriver $db)
    {
        $this->db = $db;
        if (!($this->db instanceof DbDriver))
        {
            throw new \RuntimeException('Database not defined', 500);
        }

        $this->table_name = ($item = App::config()->get(Config::SESSION_SECTION, 'table_name'))
            ? $item
            : $this->table_name;

        parent::__construct($request);
    }

    /**
     * Init session
     * @return bool|mixed
     */
    protected function create()
    {
        if (parent::create()) {
            return $this->db->createEntry(
                $this->table_name,
                $this->getDataForStore('create'),
                'id'
            );
        }
        return false;
    }

    /**
     * Save data to DB
     * @return bool
     */
    protected function save()
    {
        if (parent::save())
        {
            $this->updated = time();
            if ($this->db->updateEntry(
                $this->table_name,
                $this->getDataForStore('update'),
                ['id' => $this->id]
            )) {
                App::logger()->debug('Session [' . __CLASS__ . '] saved. id: ' . $this->id);
                return true;
            }
        }
        return false;
    }

    /**
     * Load data from DB & expiration check
     * @return bool
     */
    protected function load()
    {
        if (empty($this->id))
        {
            App::logger()->error('Session [' . __CLASS__ . '] without id not loaded');
            return false;
        }

        if ($row = $this->db->getEntry($this->table_name, ['id' => $this->id])) {
            App::logger()->debug('Session [' . __CLASS__ . '] with id ' . $this->id .  ' loaded');
            return $this->deployFromStorage($row);
        } else {
            App::logger()->error('Session [' . __CLASS__ . '] with id ' . $this->id .  ' not found in DB');
        }

        return false;
    }

    /**
     * @inherited
     *
     * @param object $data
     * @return bool
     */
    protected function deployFromStorage($data)
    {
        $this->created = strtotime($data->created);
        $this->updated = strtotime($data->updated);
        $this->ip_address = $data->ip_address;
        $this->user_agent = $data->user_agent;
        $this->data = unserialize($data->data);
        return true;
    }

    /**
     * @inherited
     *
     * @param string $operation
     * @return array
     */
    protected function getDataForStore($operation = 'create')
    {
        $a = [
            'updated' => date('Y-m-d H:i:s', $this->updated),
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'data' => serialize($this->data)
        ];
        if ($operation == 'create') {
            $a['id'] = $this->id;
            $a['created'] = date('Y-m-d H:i:s', $this->created);
        }
        return $a;
    }

    /**
     * Garbage collection
     *
     * This deletes expired session rows from database
     * if the probability percentage is met
     *
     * @return bool
     */
    protected function gc()
    {
        $probability = ini_get('session.gc_probability');
        $divisor = ini_get('session.gc_divisor');

        if (mt_rand(1, $divisor) <= $probability)
        {
            $expire = time() - $this->expiration;
            $this->db->query(
                'DELETE' . ' FROM ' . $this->table_name . ' WHERE updated < ?',
                [date('Y-m-d H:i:s', $expire)]
            );
            App::logger()->debug('Garbage collector performed');
        }

        return true;
    }

    public function destroy()
    {
        return (parent::destroy() && $this->db->deleteEntry($this->table_name, ['id' => $this->id]));
    }

    public function close()
    {
        $this->save();
        $this->gc();
    }
}