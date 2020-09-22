<?php declare(strict_types=1);

namespace LDL\Session;

class SessionManager implements SessionManagerInterface
{

    public const SESSION_NAME_DEFAULT = 'LDL_SESSION';

    /**
     * @var Handler\SessionHandlerInterface
     */
    private $handler;

    /**
     * @var array
     */
    private $options;

    /**
     * @var bool
     */
    private $started;

    /**
     * SessionManager constructor.
     *
     * @param Handler\SessionHandlerInterface|null $handler
     * @param string|null $name
     * @param array $options
     *
     * @see http://php.net/manual/en/session.configuration.php for $options
     */
    public function __construct(
        Handler\SessionHandlerInterface $handler=null,
        array $options=[],
        string $name=null
    ) {
        $this->started = false;
        $this->handler = $handler;
        \session_set_save_handler($handler ?? new Handler\File\FileSessionHandler(), true);
        \session_name($name ?? self::SESSION_NAME_DEFAULT);
        $this->options = $options;
    }

    public function start(): bool
    {
        foreach($this->options as $o=>$v){
            ini_set($o, (string) $v);
        }

        if($this->started){
            $msg = 'Session was already started';
            throw new Exception\SessionAlreadyStartedException($msg);
        }

        if(false === \session_start()){
            $msg='Unable to start session';
            throw new Exception\SessionStartException($msg);
        }

        $this->started = true;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getId() : string
    {
        return \session_id() ?: '';
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions(): array
    {
        $config = [];

        foreach (\ini_get_all('session') as $key => $value) {
            $config[\substr($key, 8)] = $value['local_value'];
        }

        return $config;
    }

    /**
     * {@inheritdoc}
     */
    public function setId(string $id): SessionManagerInterface
    {
        \session_id($id);
        return $this;
    }

    public function has(string $key) : bool
    {
        return \array_key_exists($key, $_SESSION);
    }

    /**
     * {@inheritdoc}
     */
    public function isStarted(): bool
    {
        return \PHP_SESSION_ACTIVE === \session_status();
    }

    public function clear() : SessionManagerInterface
    {
        $_SESSION = [];
        return $this;
    }

    public function set($key, $value) : SessionManagerInterface
    {
        if(false === $this->started){
            $this->start();
        }

        $_SESSION[$key] = $value;
        return $this;
    }

    public function unset($key) : bool
    {
        if($this->has($key)){
            unset($_SESSION[$key]);
            return true;
        }

        return false;
    }

    public function destroy() : SessionManagerInterface
    {
        if(!$this->isStarted()){
            $msg = 'Session was not started, there is nothing to be destroyed';
            throw new Exception\SessionNotStartedException($msg);
        }

        $this->clear();

        if (\ini_get('session.use_cookies')) {
            $params = \session_get_cookie_params();
            \setcookie(
                $this->getName(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        if ($this->isStarted()) {
            \session_destroy();
            \session_unset();
        }

        \session_write_close();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return \session_name();
    }

    public function getHandler() : Handler\SessionHandlerInterface
    {
        return $this->handler;
    }

    public function get($key, $default=null)
    {
        return $this->has($key) ? $_SESSION[$key] : $default;
    }

    public function save(): SessionManagerInterface
    {
        \session_write_close();
        return $this;
    }

    public function replace(array $values): SessionManagerInterface
    {
        $_SESSION = array_replace_recursive($_SESSION, $values);
        return $this;
    }
    public function count() : int
    {
        return \count($_SESSION);
    }

    public function regenerateId(bool $deleteOldSession=true): bool
    {
        return \session_regenerate_id($deleteOldSession);
    }

    /**
     * {@inheritdoc}
     */
    public function setCookieParams(
        int $lifetime,
        string $path = null,
        string $domain = null,
        bool $secure = false,
        bool $httpOnly = false
    ): SessionManagerInterface
    {
        \session_set_cookie_params($lifetime, $path, $domain, $secure, $httpOnly);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCookieParams(): array
    {
        return \session_get_cookie_params();
    }
}
