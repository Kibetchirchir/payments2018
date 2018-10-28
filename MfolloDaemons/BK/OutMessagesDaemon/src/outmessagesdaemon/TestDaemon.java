/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

package outmessagesdaemon;
import outmessagesdaemon.db.MySQL;
import outmessagesdaemon.utils.Logging;
import outmessagesdaemon.utils.Props;
import java.sql.SQLException;

/**
 * <p>Java UNIX daemon test file.</p> <p>Title: TestBeepMessaging.java</p>
 * <p>Description: This class is used to test the functionality of the Java
 * Daemon.</p> <p>Created on 21 March 2012, 10:48</p> <hr />
 *
 * @since 1.0
 * @author <a href="brian.ngure@cellulant.com">Brian Ngure</a>
 * @version Version 1.0
 */
@SuppressWarnings({"ClassWithoutLogger", "FinalClass"})
public final class TestDaemon {

    /**
     * Logger for this application.
     */
    private static Logging log;
    /**
     * Loads system properties.
     */
    private static Props props;
    /**
     * Initializes the MySQL connection pool.
     */
    private static MySQL mysql;
    /**
     * The main run class.
     */
    private static ProcessHandler messaging;
    /**
     * The string to append before the string being logged.
     */
    private static String logPreString = "TestBeepMessaging | ";

    /**
     * Private constructor.
     */
    private TestDaemon() {
    }
    DaemonRun d = new DaemonRun();

    /**
     * Test init().
     */
    public static void init() {

        props = new Props();
        log = new Logging(props);

        try {
            mysql = new MySQL(props.getDbHost(), props.getDbPort(),
                props.getDbName(), props.getDbUserName(),
                props.getDbPassword(), props.getDbPoolName(), 20);
            //load a properties file

            logPreString = logPreString + "init() | -1 | ";

        } catch (ClassNotFoundException ex) {
            log.fatal(logPreString + "Exception caught during initialization" + ex);
        } catch (InstantiationException ex) {
            log.fatal(logPreString + "Exception caught during initialization" + ex);
        } catch (IllegalAccessException ex) {
            log.fatal(logPreString + "Exception caught during initialization" + ex);
        } catch (SQLException ex) {
            log.fatal(logPreString + "Exception caught during initialization" + ex);
        }

        messaging = new ProcessHandler(props, log, mysql);
    }

    /**
     * Main method.
     *
     * @param args command line arguments
     */
    @SuppressWarnings({"SleepWhileInLoop", "UseOfSystemOutOrSystemErr"})
    public static void main(final String[] args) {
        System.out.println("Please use /etc/init.d/beepMessaging start|stop");
//        System.exit(-1, 1);

        logPreString = logPreString + "main() | -1 | ";
        init();

        while (true) {
            log.info("");

            try {
                messaging.runDaemon();
            } catch (Exception ex) {
                log.fatal(logPreString + "Error occured: " + ex);
            }

            try {
                Thread.sleep(1000);
            } catch (InterruptedException ex) {
                System.err.println("Error: " + ex);
            }
        }
    }
}
