<?php
namespace Common\Exception;

class BusinessException extends \Exception {
    
    /**
     *
     * @param string $message            
     * @param int $code
     *            NOTE: The code MUST be greater than 1000 for business exception.
     */
    public function __construct($message, $code = 0) {
        parent::__construct ( $message, $code );
    }
}

?>