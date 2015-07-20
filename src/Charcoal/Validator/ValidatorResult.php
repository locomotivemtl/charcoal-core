<?php

namespace Charcoal\Validator;

use \DateTime as DateTime;
use \DateTimeInterface as DateTimeInterface;
use \InvalidArgumentException as InvalidArgumentException;

/**
* A Validator Result object.
*
* @todo Change the visibility of the members to private. (Require custom encoder to output them)
*/
class ValidatorResult
{
    /**
    * @var string $ident
    */
    private $ident;
    /**
    * Can be `notice`, `warning` or `error`
    * @var string $level
    */
    private $level;
    /**
    * @var string $message
    */
    private $message;
    /**
    * @var DateTime $ts
    */
    private $ts;

    /**
    * @param array $data Optional
    */
    public function __construct(array $data = null)
    {
        $ts = new DateTime();
        $this->set_ts($ts);

        if (is_array($data)) {
            $this->set_data($data);
        }
    }

    /**
    * @param array $data
    * @return ValidatorResult Chainable
    */
    public function set_data(array $data)
    {
        if (isset($data['ident'])) {
            $this->set_ident($data['ident']);
        }
        if (isset($data['level']) && $data['message'] !== null) {
            $this->set_level($data['level']);
        }
        if (isset($data['message']) && $data['message'] !== null) {
            $this->set_message($data['message']);
        }
        if (isset($data['ts']) && $data['ts'] !== null) {
            $this->set_ts($data['ts']);
        }
        return $this;
    }

    /**
    * @param string $ident
    * @throws InvalidArgumentException if parameter is not valid
    * @return ValidatorResult
    */
    public function set_ident($ident)
    {
        if (!is_string($ident)) {
            throw new InvalidArgumentException('Ident must be a string.');
        }
        $this->ident = $ident;
        return $this;
    }

    /**
    * @return string
    */
    public function ident()
    {
        return $this->ident;
    }

    /**
    * @param string $level
    * @throws InvalidArgumentException if parameter is not valid
    * @return ValidatorResult
    */
    public function set_level($level)
    {
        if (!is_string($level)) {
            throw new InvalidArgumentException('Level must be a string.');
        }
        if (!in_array($level, ['notice', 'warning', 'error'])) {
            throw new InvalidArgumentException('Level can only be notice, warning or error.');
        }
        $this->level = $level;
        return $this;
    }

    /**
    * @return string
    */
    public function level()
    {
        return $this->level;
    }

    /**
    * @param string $message
    * @throws InvalidArgumentException if parameter is not valid
    * @return ValidatorResult
    */
    public function set_message($message)
    {
        if (!is_string($message)) {
            throw new InvalidArgumentException('Message must be a string.');
        }
        $this->message = $message;
        return $this;
    }

    /**
    * @return string
    */
    public function message()
    {
        return $this->message;
    }

    /**
    * @param string|DateTime $ts
    * @throws InvalidArgumentException if parameter is not valid
    * @return ValidatorResult
    */
    public function set_ts($ts)
    {
        if (is_string($ts)) {
            $ts = new DateTime($ts);
        }
        if (!($ts instanceof DateTimeInterface)) {
            throw new InvalidArgumentException(
                'Invalid "Timestamp" value. Must be a date/time string or a DateTime object.'
            );
        }
        $this->ts = $ts;
        return $this;
    }

    /**
    * @return DateTime
    */
    public function ts()
    {
        return $this->ts;
    }
}
