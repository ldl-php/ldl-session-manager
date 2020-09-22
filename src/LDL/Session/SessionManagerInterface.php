<?php declare(strict_types=1);

namespace LDL\Session;

interface SessionManagerInterface
{
    /**
     * Starts the session - do not use session_start().
     *
     * @return bool True if session started
     */
    public function start(): bool;

    /**
     * Checks if the session was started.
     */
    public function isStarted(): bool;

    /**
     * Migrates the current session to a new session id while maintaining all session attributes.
     *
     * Regenerates the session ID - do not use session_regenerate_id(). This method can optionally
     * change the lifetime of the new cookie that will be emitted by calling this method.
     *
     * @return bool True if session migrated, false if error
     */
    public function regenerateId() : bool;


    /**
     * @return SessionManagerInterface
     */
    public function destroy() : SessionManagerInterface;

    /**
     * Returns the session ID.
     *
     * @return string|null The session ID
     */
    public function getId() :?string;

    /**
     * Sets the session ID.
     *
     * @return SessionManagerInterface
     */
    public function setId(string $id) : SessionManagerInterface;

    /**
     * Returns the session name.
     *
     * @return string The session name
     */
    public function getName(): string;

    /**
     * Returns true if the attribute exists.
     *
     * @param string $name
     * @return bool true if the attribute is defined, false otherwise
     */
    public function has(string $name): bool;

    /**
     * Gets an attribute by key.
     *
     * @param string     $name    The attribute name
     * @param mixed|null $default The default value if not found
     *
     * @return mixed|null
     */
    public function get(string $name, $default = null);

    /**
     * Sets an attribute by key.
     *
     * @param string $name
     * @param string $value
     *
     * @return void
     */
    public function set(string $name, $value);

    /**
     * Sets multiple attributes at once: takes a keyed array and sets each key => value pair.
     *
     * @param array $values
     * @return void
     */
    public function replace(array $values);

    /**
     * Deletes an attribute by key.
     * @param mixed $name
     * @return bool
     */
    public function unset($name): bool;

    /**
     * Clear all attributes.
     */
    public function clear() : SessionManagerInterface;

    /**
     * Returns the number of attributes.
     */
    public function count(): int;

    /**
     * Force the session to be saved and closed.
     *
     * This method is generally not required for real sessions as the session
     * will be automatically saved at the end of code execution.
     *
     * @return void
     */
    public function save();

    /**
     * Get session runtime options.
     *
     * @return array
     */
    public function getOptions() : array;

    /**
     * Set cookie parameters.
     *
     * @see http://php.net/manual/en/function.session-set-cookie-params.php
     *
     * @param int    $lifetime the lifetime of the cookie in seconds
     * @param string $path     the path where information is stored
     * @param string $domain   the domain of the cookie
     * @param bool   $secure   the cookie should only be sent over secure connections
     * @param bool   $httpOnly the cookie can only be accessed through the HTTP protocol
     *
     * @return SessionManagerInterface
     */
    public function setCookieParams(
        int $lifetime,
        string $path,
        string $domain,
        bool $secure,
        bool $httpOnly
    ): SessionManagerInterface;

    /**
     * Get cookie parameters.
     *
     * @see http://php.net/manual/en/function.session-get-cookie-params.php
     */
    public function getCookieParams(): array;
}
