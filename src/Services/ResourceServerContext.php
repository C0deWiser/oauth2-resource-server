<?php

namespace Codewiser\ResourceServer\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * Store some data in session
 *
 * @property string $state
 * @property string $response_type
 * @property string $return_path
 * @property boolean $run_in_popup
 * @property string $access_token
 */
class ResourceServerContext
{
    protected $ttl = '3600';
    protected $stateValue;
    protected $contextData = array();
    /**
     * Эти ключи сохраняем в контексте (то есть с привязкой к state), остальные — просто в сессии
     * @var array
     */
    protected $contextKeys = array('response_type', 'return_path', 'run_in_popup');

    protected function sessionSet($name, $value)
    {
        Session::put($name, $value);
    }
    protected function sessionGet($name)
    {
        return Session::get($name);
    }
    protected function sessionHas($name)
    {
        return Session::has($name);
    }
    protected function sessionDel($name)
    {
        Session::remove($name);
    }

    protected function sessionKey($state)
    {
        return "oauth2:state:{$state}";
    }

    /**
     * Context saved under state value.
     */
    protected function saveIfPossible()
    {
        if ($this->stateValue) {
            $this->contextData['issued_at'] = time();
            $this->sessionSet($this->sessionKey($this->stateValue), serialize($this->contextData));
            Session::save();
        }
    }

    /**
     * Context saved under state value.
     *
     * @param $state
     * @return boolean
     */
    protected function loadIfPossible($state)
    {
        $key = $this->sessionKey($state);

        if ($this->sessionHas($key)) {
            $contextData = unserialize($this->sessionGet($key));
            $this->sessionDel($key);
            if ($contextData && ($contextData['issued_at'] + (int)$this->ttl) > time()) {
                $this->stateValue = $state;
                $this->contextData = $contextData;
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @param $state
     * @return bool
     */
    public function restoreContext($state)
    {
        return $this->loadIfPossible($state);
    }

    public function __get($name)
    {
        if ($name == 'state') {
            return $this->stateValue;
        } elseif (in_array($name, $this->contextKeys)) {
            return isset($this->contextData[$name]) ? $this->contextData[$name] : null;
        } else {
            return $this->sessionGet($name);
        }
    }
    public function __set($name, $value)
    {
        if ($name == 'state') {
            $this->stateValue = $value;
            $this->saveIfPossible();
        } elseif (in_array($name, $this->contextKeys)) {
            $this->contextData[$name] = $value;
            $this->saveIfPossible();
        } else {
            $this->sessionSet($name, $value);
        }
    }
    public function __isset($name)
    {
        if ($name == 'state') {
            return (boolean)$this->stateValue;
        } elseif (in_array($name, $this->contextKeys)) {
            return (isset($this->contextData[$name]) && $this->contextData[$name]) ? true : false;
        } else {
            return $this->sessionHas($name);
        }
    }
    public function __unset($name)
    {
        if ($name == 'state') {
            if ($this->stateValue) {
                $this->sessionDel($this->sessionKey($this->stateValue));
                $this->stateValue = null;
            }
        } elseif (in_array($name, $this->contextKeys)) {
            if (isset($this->contextData[$name])) {
                unset($this->contextData[$name]);
            }
            $this->saveIfPossible();
        } else {
            $this->sessionDel($name);
        }
    }

    /**
     * Clear context
     */
    public function clearContext()
    {
        if ($this->stateValue) {
            $this->sessionDel($this->sessionKey($this->stateValue));
            $this->stateValue = null;
        }
        $this->contextData = array();
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        if ($this->stateValue) {
            return array_merge(array('state' => $this->stateValue), $this->contextData);
        } else {
            return $this->contextData;
        }
    }
}
