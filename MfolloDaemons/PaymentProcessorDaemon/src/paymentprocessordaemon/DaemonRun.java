package paymentprocessordaemon;

import paymentprocessordaemon.db.MySQL;
import paymentprocessordaemon.utils.Logging;
import paymentprocessordaemon.utils.Props;
import java.io.IOException;
import java.sql.SQLException;

import org.apache.commons.daemon.Daemon;
import org.apache.commons.daemon.DaemonContext;
import org.apache.commons.daemon.DaemonInitException;

/**
 * <p>DaemonRun RTPP daemon.</p> <p>Title: DaemonRun.java</p> <p>Description:
 * This class implements the following methods to enable the Java Daemon:<br
 * /><br /> <ul> <li>void init(String[] arguments): Here open configuration
 * files, create a trace file, create ServerSockets, Threads, etc</li> <li>void
 * start(): Start the Thread, accept incoming connections, etc</li> <li>void
 * stop(): Inform the Thread to terminate the run(), close the ServerSockets, db
 * connections, etc</li> <li>void destroy(): Destroy any object created in
 * init()</li> </ul> </p> <p>Created on 16 August 2010, 23:48</p> <p>Copyright:
 * Copyright (c) 2012, <a href="mailto:brian@pixie.co.ke">Brian Ngure</a></p>
 * <hr /> <i> This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or (at your
 * option) any later version. </i> <p>&nbsp;</p> <i> This library is distributed
 * in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details. </i> <p>&nbsp;</p> <i>
 * You should have received a copy of the GNU General Public License along with
 * this library; if not, see <http://www.gnu.org/licenses/>. </i> <hr />
 *
 * @since 1.0
 * @author <a href="brian@pixie.co.ke">Brian Ngure</a>
 * @version Version 1.0
 */
@SuppressWarnings({"ClassWithoutLogger", "FinalClass"})
public final class DaemonRun implements Daemon, Runnable {

    /**
     * The worker thread that does all the work.
     */
    private transient Thread worker;
    /**
     * Flag to check if the worker thread should run.
     */
    private transient boolean working = false;
    /**
     * Logger for this application.
     */
    private transient Logging log;
    /**
     * The main run class.
     */
    private transient ProcessHandler messaging;
    /**
     * Properties instance.
     */
    private transient Props props;
    /**
     * Initializes the MySQL connection pool.
     */
    private MySQL mysql;
    /**
     * The string to append before the string being logged.
     */
    private String logPreString;

    /**
     * Used to read configuration files, create a trace file, create
     * ServerSockets, Threads, etc.
     *
     * @param context the DaemonContext
     *
     * @throws DaemonInitException on error
     */
    @Override
    public void init(final DaemonContext context) throws DaemonInitException, IOException {
        worker = new Thread(this);
        props = new Props();

        try {
            mysql = new MySQL(props.getDbHost(), props.getDbPort(),
                props.getDbName(), props.getDbUserName(),
                props.getDbPassword(), props.getDbPoolName(), 100);
            this.logPreString = "DaemonRun | ";
            String logPreString = this.logPreString + "init() | -1 | ";
        } catch (ClassNotFoundException ex) {
            log.fatal(logPreString
                + "Exception caught during initialization" + ex.getMessage());
        } catch (InstantiationException ex) {
            log.fatal(logPreString
                + "Exception caught during initialization" + ex.getMessage());
        } catch (IllegalAccessException ex) {
            log.fatal(logPreString
                + "Exception caught during initialization" + ex.getMessage());
        } catch (SQLException ex) {
            log.fatal(logPreString
                + "Exception caught during initialization" + ex.getMessage());
        }

        log = new Logging(props);
        log.info(logPreString + "Initializing Beep Messaging daemon...");




        messaging = new ProcessHandler(props, log, mysql);
    }

    /**
     * Starts the daemon.
     */
    @Override
    public void start() {
        working = true;
        worker.start();
        String logPreString = this.logPreString + "start() | -1 | ";
        log.info(logPreString + "Starting Beep Messaging daemon...");
    }

    /**
     * Stops the daemon. Informs the thread to terminate the run().
     */
    @Override
    @SuppressWarnings("SleepWhileInLoop")
    public void stop() {
        String logPreString = this.logPreString + "stop() | -1 | ";
        log.info(logPreString
            + "Stopping Beep Messaging daemon...");

        working = false;

        while (!messaging.getIsCurrentPoolShutDown()) {
            log.info(logPreString
                + "Waiting for current thread pool to complete tasks...");
            try {
                Thread.sleep(2000);
            } catch (InterruptedException ex) {
                log.error(logPreString
                    + "InterruptedException occured while waiting for "
                    + "tasks to complete: "
                    + "Exception caught during initialization" + ex.getMessage());
            }
        }

        log.info(logPreString
            + "Completed tasks in current thread pool, continuing daemon "
            + "shutdown");

        log.info(logPreString + "Beep Messaging Daemon stopped.");
    }

    /**
     * Destroys the daemon. Destroys any object created in init().
     */
    @Override
    public void destroy() {
        String logPreString = this.logPreString + "destroy() | -1 | ";
        log.info(logPreString
            + "Destroying Beep Messaging daemon...");
        log.info(logPreString + "Exiting...");
    }

    /**
     * Runs the thread. The application runs inside an "infinite" loop.
     */
    @Override
    @SuppressWarnings({"SleepWhileHoldingLock", "SleepWhileInLoop"})
    public void run() {
        while (working) {
            String logPreString = this.logPreString + "run() | -1 | ";
            log.info(logPreString + "");

            try {
                messaging.runDaemon();
            } catch (Exception ex) {
                log.fatal(logPreString + "Error occured: "
                    + ex.getMessage());
            }

            try {
                Thread.sleep(props.getSleepTime());
            } catch (InterruptedException ex) {
                log.error(logPreString + "Exception caught: "
                    + ex.getMessage());
            }
        }
    }
}
