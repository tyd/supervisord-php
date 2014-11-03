<?php
/**
 * supervisord-php
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * https://github.com/tyd/supervisord-php/blob/master/LICENSE
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to tyler@logicmasterminds.com so we can send you a copy immediately.
 *
 * @category supervisord-php
 * @package supervisord-php
 * @author Tyler Davis <tyler@logicmasterminds.com>
 * @copyright Copyright (c) 2012 Tyler Davis (http://tyd.github.com/)
 * @license   https://github.com/tyd/supervisord-php/blob/master/LICENSE New BSD License
 */

class Supervisord {
    protected $_url = '';
    protected $_path = '/RPC2';
    protected $_host = '';
    protected $_port = '';
    protected $_headers = array('Content-Type: text/xml');

	/**
	 *
	 * @param string $host
	 * @param int $port
	 * @param string $username
	 * @param string $password
	 */
    public function __construct($host = '127.0.0.1', $port = 9001, $username = '', $password = '') {
        $this->_url = 'http://' . $host . ':' . $port . $this->_path;

        // Basic auth?
        if ($username != '' && $password != '')
            $this->_haeders[] = 'Authorization: Basic '.base64_encode($username.':'.$password);
    }

    /**
     * Make RPC-XML request
     *
     * @param string $method
     * @param mixed $params
     */
    protected function _call($method, $params = array()) {
        $request = xmlrpc_encode_request($method, $params);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => $this->_haeders,
                'content' => $request
            )
        );

        $context  = stream_context_create($options);
        $file     = file_get_contents($this->_url, false, $context);
        $response = xmlrpc_decode(trim($file));

        if (!$response)
            throw new Exception('Invalid response from '.$this->_url);

        if (is_array($response) && xmlrpc_is_fault($response))
            throw new Exception($response['faultString'], $response['faultCode']);

        return $response;
    }

    /*
    ------------------------------------------------------------
    Status and Control
    ------------------------------------------------------------
    */

    /**
     * This API is versioned separately from Supervisor itself. The
     * API version returned by getAPIVersion only changes when
     * the API changes. Its purpose is to help the client
     * identify with which version of the Supervisor API it is
     * communicating.
     *
     * When writing software that communicates with this API, it is
     * highly recommended that you first test the API version for
     * compatibility before making method calls.
     *
     * @return string version id
     */
    public function getAPIVersion() {
        return $this->_call('supervisor.getAPIVersion', array());
    }

    /**
     * Return the version of the supervisor package in use by supervisord
     *
     * @return string version id
     */
    public function getSupervisorVersion() {
        return $this->_call('supervisor.getSupervisorVersion', array());
    }

    /**
     * Return identifiying string of supervisord
     *
     * This method allows the client to identify with which Supervisor
     * instance it is communicating in the case of environments where
     * multiple Supervisors may be running.
     *
     * The identification is a string that must be set in SupervisorÕs
     * configuration file. This method simply returns that
     * value back to the client.
     *
     * @return string identifying string
     */
    public function getIdentification() {
        return $this->_call('supervisor.getIdentification', array());
    }

    /**
     * Return current state of supervisord as a struct
     *
     * statecode 	statename 	Description
     * 		2 		FATAL 		Supervisor has experienced a serious error.
     * 		1 		RUNNING 	Supervisor is working normally.
     * 		0 		RESTARTING 	Supervisor is in the process of restarting.
     * 		-1 		SHUTDOWN 	Supervisor is in the process of shutting down.
     *
     * @return array An array with keys string statecode, int statename
     */
    public function getState() {
        return $this->_call('supervisor.getState', array());
    }

    /**
     * Return the PID of supervisord
     *
     * @return int PID
     */
    public function getPID() {
        return $this->_call('supervisor.getPID', array());
    }

    /**
     * Read length bytes from the main log starting at offset
     *
     * It can either return the entire log, a number of characters
     * from the tail of the log, or a slice of the log specified by
     * the offset and length parameters
     *
     * http://supervisord.org/api.html#supervisor.rpcinterface.SupervisorNamespaceRPCInterface.readLog
     *
     * @param int $offset Offset to start reading from
     * @param int $length Number of bytes to read from the log
     */
    public function readLog($offset, $length) {
        return $this->_call('supervisor.readLog', array(
            $offset,
            $length
        ));
    }

    /**
     * Clear the main log.
     *
     * @return boolean always returns True unless error
     */
    public function clearLog() {
        return $this->_call('supervisor.clearLog', array());
    }

    /**
     * Shut down the supervisor process
     *
     * @return boolean always returns True unless error
     */
    public function shutdown() {
        return $this->_call('supervisor.shutdown', array());
    }

    /**
     * Restart the supervisor process
     *
     * @return boolean always return True unless error
     */
    public function restart() {
        return $this->_call('supervisor.restart', array());
    }

    /*
    ------------------------------------------------------------
    Process Control
    ------------------------------------------------------------
    */

    /**
     * Get info about a process
     *
     * @param string $name The name of the process (or Ôgroup:nameÕ)
     * @return array An array containing data about the process
     */
    public function getProcessInfo($name) {
        return $this->_call('supervisor.getProcessInfo', array(
            $name
        ));
    }

    /**
     * Get info about all processes
     *
     * @return array An array of process status results
     */
    public function getAllProcessInfo() {
        return $this->_call('supervisor.getAllProcessInfo', array());
    }

    public function startProcess($name, $wait = true) {
        return $this->_call('supervisor.startProcess', array(
            $name,
            $wait
        ));
    }

    /**
     * Start a process
     *
     * @param string $name Process name (or Ôgroup:nameÕ, or Ôgroup:*Ô)
     * @param boolean $wait Wait for process to be fully started
     * @return boolean Always true unless error
     */
    public function startAllProcesses($wait = true) {
        return $this->_call('supervisor.startAllProcesses', array(
            $wait
        ));
    }

    /**
     * Start all processes in the group named ÔnameÕ
     *
     * @param string $name The group name
     * @param boolean $wait Wait for each process to be fully started
     * @return array An array containing start statuses
     */
    public function startProcessGroup($name, $wait = true) {
        return $this->_call('supervisor.startProcessGroup', array(
            $name,
            $wait
        ));
    }

    /**
     * Stop all processes in the process group named ÔnameÕ
     *
     * @param string $name The group name
     * @param boolean $wait Wait for each process to be fully stopped
     * @return boolean Always return true unless error.
     */
    public function stopProcessGroup($name, $wait = true) {
        return $this->_call('supervisor.stopProcessGroup', array(
            $name,
            $wait
        ));
    }

    /**
     * Send a string of chars to the stdin of the process name.
     *
     * If non-7-bit data is sent (unicode), it is encoded to utf-8 before being sent to
     * the processÕ stdin. If chars is not a string or is not unicode, raise
     * INCORRECT_PARAMETERS. If the process is not running, raise NOT_RUNNING.
     * If the processÕ stdin cannot accept input (e.g. it was closed by
     * the child process), raise NO_FILE.
     *
     * @param string $name The process name to send to (or Ôgroup:nameÕ)
     * @param string $chars The character data to send to the process
     * @return boolean Always return True unless error
     */
    public function sendProcessStdin($name, $chars) {
        return $this->_call('supervisor.sendProcessStdin', array(
            $name,
            $chars
        ));
    }

    /**
     * Send an event that will be received by event listener subprocesses
     * subscribing to the RemoteCommunicationEvent.
     *
     * @param string $type String for the ÒtypeÓ key in the event header
     * @param string $data Data for the event body
     * @return boolean Always return True unless error
     */
    public function sendRemoteCommEvent($type, $data) {
        return $this->_call('supervisor.sendRemoteCommEvent', array(
            $type,
            $data
        ));
    }

    /**
     * Update the config for a running process from config file.
     *
     * @param string $name name name of process group to add
     * @return boolean True if successful
     */
    public function addProcessGroup($name) {
        return $this->_call('supervisor.addProcessGroup', array(
            $name
        ));
    }

    /**
     * Remove a stopped process from the active configuration.
     *
     * @param string $name name of process group to remove
     * @return boolean Indicates whether the removal was successful
     */
    public function removeProcessGroup($name) {
        return $this->_call('supervisor.removeProcessGroup', array(
            $name
        ));
    }

    /*
    ------------------------------------------------------------
    Process Logging
    ------------------------------------------------------------
    */

    /**
     * Read length bytes from nameÕs stdout log starting at offset
     *
     * @param string $name The name of the process (or Ôgroup:nameÕ)
     * @param int $offset Offset offset to start reading from.
     * @param int $length Length number of bytes to read from the log.
     * @return string Bytes of log
     */
    public function readProcessStdoutLog($name, $offset, $length) {
        return $this->_call('supervisor.readProcessStdoutLog', array(
            $name,
            $offset,
            $length
        ));
    }

    /**
     * Read length bytes from nameÕs stderr log starting at offset
     *
     * @param string $name The name of the process (or Ôgroup:nameÕ)
     * @param int $offset Offset offset to start reading from.
     * @param int $length Length number of bytes to read from the log.
     * @return string Bytes of log
     */
    public function readProcessStderrLog($name, $offset, $length) {
        return $this->_call('supervisor.readProcessStderrLog', array(
            $name,
            $offset,
            $length
        ));
    }

    /**
     * Provides a more efficient way to tail the (stdout) log than
     * readProcessStdoutLog(). Use readProcessStdoutLog() to read
     * chunks and tailProcessStdoutLog() to tail.
     *
     * @param string $name The name of the process (or Ôgroup:nameÕ)
     * @param int $offset Offset offset to start reading from.
     * @param int $length Length number of bytes to read from the log.
     * @return string Bytes of log
     */
    public function tailProcessStderrLog($name, $offset, $length) {
        return $this->_call('supervisor.tailProcessStderrLog', array(
            $name,
            $offset,
            $length
        ));
    }

    /**
     * Clear the stdout and stderr logs for the named process and reopen them.
     *
     * @param string $name The name of the process (or Ôgroup:nameÕ)
     * @return boolean Always return True unless error
     */
    public function clearProcessLogs($name) {
        return $this->_call('supervisor.clearProcessLogs', array(
            $name
        ));
    }

    /**
     * Clear all process log files
     *
     * @return boolean Always return True unless error
     */
    public function clearAllProcessLogs() {
        return $this->_call('supervisor.clearAllProcessLogs', array());
    }

    /*
    ------------------------------------------------------------
    System Methods
    ------------------------------------------------------------
    */

    /**
     * Return an array listing the available method names
     *
     * @return array An array of method names available (strings).
     */
    public function listMethods() {
        return $this->_call('system.listMethods', array());
    }

    /**
     * Return a string showing the methodÕs documentation
     *
     * @param string $name
     * @return string The documentation for the method name.
     */
    public function methodHelp($name) {
        return $this->_call('system.methodHelp', array(
            'supervisor.'.$name
        ));
    }

    /**
     * Return an array describing the method signature in the
     * form [rtype, ptype, ptype...] where rtype is the return
     * data type of the method, and ptypes are the parameter data
     * types that the method accepts in method argument order.
     *
     * @param string $name
     * @return array The result.
     */
    public function methodSignature($name) {
        return $this->_call('system.methodSignature', array(
            'supervisor.'.$name
        ));
    }

	/**
	 * @TODO Need to figure this out
	 * Process an array of calls, and return an array of results.
	 *
	 * $calls should be ar array of the form array('methodName' => 'clearAllProcessLogs', 'params' => array() )
	 *
	 * Each result will either be a single-item array containing the result value,
	 * or an array of the form array('faultCode' => <int>, 'faultString' => <string> ).
	 *
	 * This is useful when you need to make lots of small calls without lots of round trips.
	 *
	 * @param array $calls
	 */
    /*
    public function multicall(array $calls) {
        return $this->_call('system.multicall', array($calls));
    }
    */
}
