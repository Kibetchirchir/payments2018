/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package outmessagesdaemon;

import outmessagesdaemon.db.MySQL;
import outmessagesdaemon.utils.Logging;
import outmessagesdaemon.utils.Props;
import outmessagesdaemon.utils.OutMessagingConstants;
import java.io.BufferedReader;
import java.io.DataInputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStreamReader;
import java.io.PrintWriter;
import java.net.Socket;
import java.net.SocketException;
import java.net.UnknownHostException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.ArrayList;
import java.util.List;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.concurrent.TimeUnit;

/**
 *
 * @Evid Araka Sibi
 */
public class ProcessHandler {

 
  /**
     * The MySQL data source.
     */
    private transient MySQL mysql;
    /**
     * File input stream to check for failed queries.
     */
    private FileInputStream fin;
    /**
     * Data input stream to check for failed queries.
     */
    private DataInputStream in;
    /**
     * Buffered reader to check for failed queries.
     */
    private BufferedReader br;
    /**
     * The daemons current state.
     */
    private transient int daemonState;
    /**
     * System properties class instance.
     */
    private transient Props props;
    /**
     * Log class instance.
     */
    private transient Logging logging;
    /**
     * The print out to external .TXT file
     */
    private transient PrintWriter pout;
    /**
     * Flag to check if current pool is completed.
     */
    private transient boolean isCurrentPoolShutDown = false;
    /**
     * The string to append before the string being logged.
     */
    private String logPreString;
    /**
     * The current run ID.
     */
    private int runID= 50;

    public ProcessHandler(final Props properties, final Logging log, final MySQL mySQL) {

        props = properties;
        logging = log;
        mysql = mySQL;

        this.logPreString = "MessagingDaemon | ";
        String logPreString = this.logPreString + "ProcessHandler() | -1 | ";
        // Get the list of errors found when loading system properties
        List<String> loadErrors = properties.getLoadErrors();
        int sz = loadErrors.size();

        if (sz > 0) {
            log.info(logPreString + "There were exactly "
                + sz + " error(s) during the load operation...");

            for (String err : loadErrors) {
                log.fatal(logPreString + err);
            }

            log.info(logPreString + "Unable to start daemon "
                + "because " + sz + " error(s) occured during load.");
            System.exit(1);
        } else {
            log.info(logPreString
                + "All required properties were loaded successfully");
            daemonState = OutMessagingConstants.DAEMON_RUNNING;
        }
    }
public int allocateBucket(int CurrentRunID) {
        int numberOfRequestsAllocated = 0;
        String updateQuery = "update outbound set bucketID=? "
                + "where (nextSend<=now() or nextSend is NULL) "
                + "and "
                + "bucketID=? and (status=? " //unprocessed requests
                + "or status=?) and numberOfSends <= ? " //requests to be tried
                + " limit ?"; //unprocessed to be processed first
       
        Connection conn = null;
        PreparedStatement stmt = null;
        try {
            conn = mysql.getConnection();
            stmt = conn.prepareStatement(updateQuery);
            stmt.setInt(1, CurrentRunID);
            stmt.setInt(2, props.getUnprocessedStatus());
            stmt.setInt(3, props.getUnprocessedStatus());//ensures others with status 7 and same bucketID are not picked
            stmt.setInt(4, props.getRetryStatus());
            stmt.setInt(5, props.getMaxFailedQueryRetries());
            stmt.setInt(6, props.getBucketSize());
            numberOfRequestsAllocated = stmt.executeUpdate();
        } catch (SQLException e) {
            logging.fatal(this.logPreString  + "allocateBucket() --- Failed to allocate Bucket to bucketID" + this.runID + " reason=>" + e.getMessage());
            numberOfRequestsAllocated = 0;
        } finally {
            try {
                try {
                    if (stmt != null) {
                        stmt.close();
                        stmt = null;
                    }
                } catch (Exception ex) {
                    logging.fatal(this.logPreString  + "allocateBucket() ---Exception thrown while trying to free resources." + ex.getMessage());
                }

                if (conn != null) {
                    try {
                        conn.close();
                    } catch (SQLException sqle) {
                        logging.fatal(this.logPreString  + "allocateBucket() --- Failed to close connection: " + sqle.getMessage());
                    }
                }
              
            } catch (Exception exc) {
                logging.fatal(this.logPreString + " | allocateBucket() -Unable to close the database connection" + exc.getMessage() + exc.toString());
            }finally
            {
                 
            }
            return numberOfRequestsAllocated;
        }
    }
    private synchronized void executeTasks() {
        PreparedStatement stmt = null;
        ResultSet rs = null;
        Connection conn = null;
        ExecutorService executor = null;
        runID ++;
     if(this.allocateBucket(runID) > 0)
     {
         logPreString = "executeTasks() | -1 | ";

      

        String query = "SELECT o.outboundID, o.source, o.MSISDN, o.message, ap.spID, ap.netServiceID, m.sendSMSURL, o.numberOfSends "
            + "FROM outbound o " 
            + "inner join accessPoints ap on o.source = ap.shortcode " 
            + "inner join MNOs m on m.netID = ap.netID " 
            + "WHERE status = ? AND bucketID=?";


        try {
            conn = mysql.getConnection();
            stmt = conn.prepareStatement(query);
            stmt.setInt(1, props.getUnprocessedStatus());
            stmt.setInt(2, runID);

            rs = stmt.executeQuery();

            int size = 0;
            if (rs.last()) {
                size = rs.getRow();
                rs.beforeFirst();
            }
            if (size > 0) {
                logging.info(logPreString
                    + "Fetch records using query = " + query);
                isCurrentPoolShutDown = false;

                if (size <= props.getNumOfChildren()) {
                    executor = Executors
                        .newFixedThreadPool(size);
                } else {
                    executor = Executors
                        .newFixedThreadPool(props.getNumOfChildren());
                }


                while (rs.next()) {
                    int outMessageID = rs.getInt("outboundID");
                    String message = rs.getString("message");
                    String source = rs.getString("source");
                    String destination = rs.getString("MSISDN");
                    int numofSends = rs.getInt("numberOfSends");
                    String spID = rs.getString("spID");
                    String serviceID = rs.getString("netServiceID");
                    String sendSMSURL = rs.getString("sendSMSURL");
                    
                    

                 

                    // Create a runnable task and submit it

                    Runnable task = createTask(
                        outMessageID,
                        message,
                        source,
                        destination,
                        numofSends,
                        spID,
                        serviceID,
                        sendSMSURL);

                    executor.execute(task);
                }
                rs.close();
                rs = null;
                stmt.close();
                stmt = null;
                conn.close();
                conn = null;
                /*
                 * This will make the executor accept no new threads and
                 * finish all existing threads in the queue.
                 */
                shutdownAndAwaitTermination(executor);
            } else {
                logging.info(logPreString
                    + "No records were fetched from the DB for processing...");
            }
            
        } catch (SQLException e) {
            logging.error(logPreString + "Failed to "
                + "fetch Bucket: Select Query: " + query + " Error "
                + "Message :" + e);
        } catch (Exception e) {
            logging.error(logPreString + "Execption occured. Error "
                + "Message :" + e);
        } finally {
            isCurrentPoolShutDown = true;
            if (rs != null) {
                try {
                    rs.close();
                } catch (SQLException sqlex) {
                    logging.error(logPreString
                        + "Error closing statement: "
                        + sqlex.getMessage());
                }
            }

            if (stmt != null) {
                try {
                    stmt.close();
                } catch (SQLException sqlex) {
                    logging.error(logPreString
                        + "Failed to close statement: "
                        + sqlex.getMessage());
                }
            }

            if (conn != null) {
                try {
                    conn.close();
                } catch (SQLException sqle) {
                    logging.error(logPreString
                        + "Failed to close connection: "
                        + sqle.getMessage());
                }
            }
        }
     }
     else
     {
            logging.info(logPreString
                        + "No Records allocated  ++++++++++++ No Records to Fetch");
     }
     
    }

    /**
     * Creates a simple Runnable that holds a Job object thats worked on by the
     * child threads.
     *
     * @param outMessageID
     * @param message
     * @param source
     * @param destination
     * @param numofSends
     * @param nextSend
     * @param connectorRule
     *
     * @return a new BeepMessagingJob task
     */
    private synchronized Runnable createTask(
        final int outboundID,
        final String message,
        final String source,
        final String destination,
        final int numofSends,
        final String spID,
        final String serviceID,
        final String sendSMSURL) {
        String logPreString = this.logPreString + "createTask() | -1 | ";
        logging.info(logPreString
            + "Creating a task for message with outboundID: "
            + outboundID);
        return new MessagingJob(logging, props, mysql, outboundID, message,
            source, destination, numofSends, spID, serviceID,
            sendSMSURL);
    }

    /**
     * Process Records.
     */
    public void runDaemon() {
        String logPreString = this.logPreString + "runDaemon() | -1 | ";
        int pingState = pingDatabaseServer();
        if (pingState == OutMessagingConstants.PING_SUCCESS) {
            // The database is available, allocate, fetch and reset the bucket
            if (daemonState == OutMessagingConstants.DAEMON_RUNNING) {
                doWork();
            } else if (daemonState == OutMessagingConstants.DAEMON_RESUMING) {

                doWait(props.getSleepTime());

                logging.info(logPreString + "Resuming daemon service...");
                daemonState = OutMessagingConstants.DAEMON_RUNNING;
                logging.info(logPreString
                    + "Daemon resumed successfully, working...");
            }
        } else {
            logging.error(logPreString + "The database server: "
                + props.getDbHost() + " servicing on port: "
                + props.getDbPort() + " appears to be down. Reason: "
                + "internal function for pingDatabaseServer() returned a "
                + "PING_FAILED status.");
            daemonState = OutMessagingConstants.DAEMON_INTERRUPTED;

            logging.info(logPreString + "Connection to the database was "
                + "interrupted, suspending from service...");
            logging.info(logPreString + "Cleaning up service...");


            // Enter a Suspended state
            while (true) {
                if (daemonState == OutMessagingConstants.DAEMON_INTERRUPTED) {
                    int istate = pingDatabaseServer();
                    if (istate == OutMessagingConstants.PING_SUCCESS) {
                        daemonState = OutMessagingConstants.DAEMON_RESUMING;
                        break;
                    }
                }

                doWait(props.getSleepTime());
            }
        }
    }

    /**
     * A better functional logic that ensures secure execution of fetch bucket
     * as well as detailed management of interrupted queries. This will work
     * only when we have a db connection.
     */
    private synchronized void doWork() {
        rollbackSystem();
        executeTasks();
    }

    /**
     * Update successful transactions that were not updated.
     */
    private void rollbackSystem() {
        String logPreString = this.logPreString + "rollbackSystem() | -1 | ";
        List<String> failedQueries = checkForFailedQueries(
            OutMessagingConstants.FAILED_QUERIES_FILE);
        int failures = failedQueries.size();
        int recon = 0;

        if (failures > 0) {
            logging.info(logPreString + "I found " + failures
                + " failed update queries in file: "
                + OutMessagingConstants.FAILED_QUERIES_FILE
                + ", rolling back transactions...");

            do {
                String recon_query = failedQueries.get(recon);
                doRecon(recon_query, OutMessagingConstants.RETRY_COUNT);
                //doWait(props.getSleepTime());
                recon++;
            } while (recon != failures);

            logging.info(logPreString
                + "I have finished performing rollback...");
        }
    }

    /**
     * Loads a file with selected queries and re-runs them internally.
     *
     * @param file the file to check for failed queries
     */
    @SuppressWarnings("NestedAssignment")
    private List<String> checkForFailedQueries(final String file) {
        String logPreString = this.logPreString + "checkForFailedQueries() | -1 | ";
        List<String> queries = new ArrayList<String>(0);

        try {
            /*
             * If we fail to open the file, then the file has not been created
             * yet. This is good because it means that there is no error.
             */
            if (new File(file).exists()) {
                fin = new FileInputStream(file);
                in = new DataInputStream(fin);
                br = new BufferedReader(new InputStreamReader(in));

                String data;
                while ((data = br.readLine()) != null) {
                    if (!queries.contains(data)) {
                        queries.add(data);
                    }
                }
                br.close();
                fin.close();
                in.close();

            }
        } catch (Exception e) {
            logging.error(logPreString
                + " Error reading from FAILED_QUERIES.TXT: " + e);
        }

        return queries;
    }

    /**
     * This function determines how the queries will be re-executed i.e. whether
     * SELECT or UPDATE.
     *
     * @param query the query to re-execute
     * @param tries the number of times to retry
     */
    private void doRecon(final String query, final int tries) {
        String logPreString = this.logPreString + "doRecon() | -1 | ";
        int maxRetry = props.getMaxFailedQueryRetries();
        if (query.toLowerCase().startsWith(OutMessagingConstants.UPDATE_ID)) {
            int qstate = runUpdateRecon(query);
            if (qstate == OutMessagingConstants.UPDATE_RECON_SUCCESS) {
                logging.info(logPreString + "Re-executed this query: "
                    + query + " successfully, deleting it from file...");
                deleteQuery(OutMessagingConstants.FAILED_QUERIES_FILE, query);
            } else if (qstate == OutMessagingConstants.UPDATE_RECON_FAILED) {
                logging.info(logPreString
                    + "Failed to re-execute failed query: " + query
                    + "[Try " + tries + " out of  " + maxRetry + "]");
                int curr_try = tries + 1;
                if (tries >= maxRetry) {
                    logging.info(logPreString
                        + "Tried to re-execute failed query "
                        + props.getMaxFailedQueryRetries()
                        + " times but still failed, exiting...");
                    System.exit(1);
                } else {
                    logging.info(logPreString + "Retrying in "
                        + (props.getSleepTime() / 1000) + " sec(s) ");
                    doWait(props.getSleepTime());
                    doRecon(query, curr_try);
                }
            }
        }
    }

    /**
     * Re-executes the specified query.
     *
     * @param query the query to run
     */
    private int runUpdateRecon(final String query) {
        String logPreString = this.logPreString + "runUpdateRecon() | -1 | ";
        int result = 0;
        Statement stmt = null;
        Connection conn = null;

        try {
            conn = mysql.getConnection();
            stmt = conn.createStatement();
            stmt.executeUpdate(query);
            logging.info(logPreString + "I have just successfully "
                + "re-executed this failed query: " + query);
            result = OutMessagingConstants.UPDATE_RECON_SUCCESS;

            stmt.close();
            stmt = null;
            conn.close();
            conn = null;
        } catch (SQLException e) {
            logging.error(logPreString + "SQLException: " + e.getMessage());
            result = OutMessagingConstants.UPDATE_RECON_FAILED;
        } finally {
            if (stmt != null) {
                try {
                    stmt.close();
                } catch (SQLException sqlex) {
                    logging.error(logPreString + "runUpdateRecon --- "
                        + "Failed to close Statement object: "
                        + sqlex.getMessage());
                }
            }

            if (conn != null) {
                try {
                    conn.close();
                } catch (SQLException sqle) {
                    logging.error(logPreString + "runUpdateRecon --- "
                        + "Failed to close connection object: "
                        + sqle.getMessage());
                }
            }
        }

        return result;
    }


    /*--Delete a query from the failed_queries file after a successfull recon--*/
    public void deleteQuery(String queryfile, String query) {
        String logPreString = this.logPreString + "deleteQuery() | -1 | ";
        ArrayList<String> queries = new ArrayList<String>();
        try {
            fin = new FileInputStream(queryfile);
            in = new DataInputStream(fin);
            br = new BufferedReader(new InputStreamReader(in));

            String data = null;
            while ((data = br.readLine()) != null) {
                queries.add(data);
            }
            br.close();
            fin.close();
            in.close();
            /*--Find a match to the query--*/
            logging.info("About to remove this query: " + query
                + " from file: " + queryfile);
            if (queries.contains(query)) {
                queries.remove(query);
                logging.info("I have removed this query: " + query
                    + " from file: " + queryfile);
            }
            /*--Now save the file--*/
            pout = new PrintWriter(new FileOutputStream(queryfile, false));
            for (String new_queries : queries) {
                pout.println(new_queries);
            }
            pout.close();
        } catch (Exception e) {
            /**
             * If we fail to open it then, the file has not been created yet'
             * This is good because it means that no error(s) have been
             * experienced yet
             */
        }
    }

    /* --Sleep-Time -- */
    public void doWait(long t) {
        String logPreString = this.logPreString + "doWait() | -1 | ";
        try {
            Thread.sleep(t);
        } catch (InterruptedException ex) {
            logging.info(logPreString
                + "Thread could not sleep fpr the specified time");

            /* --DO NOTHING--*/ }
    }

    /**
     * Checks if the database server is up.
     *
     * @return state of database connection
     */
    private int pingDatabaseServer() {
        String logPreString = this.logPreString + "pingDatabaseServer() | -1 | ";
        int state;
        logging.info(logPreString + "Pinging Database...");

        try {
            String host = props.getDbHost();
            int port = Integer.parseInt(props.getDbPort());

            Socket ps = new Socket(host, port);

            /*
             * Same time we use to sleep is the same time we use for ping wait
             * period.
             */
            ps.setSoTimeout(props.getSleepTime());

            if (ps.isConnected()) {
                state = OutMessagingConstants.PING_SUCCESS;
                    logging.info(logPreString
                + "Ping is Successfull Please Carry on " );
           
                ps.close();
            } else {
                state = OutMessagingConstants.PING_FAILED;
                     logging.error(logPreString
                + "Ping Has Failed You lose" );
            }
        } catch (UnknownHostException se) {
            logging.error(logPreString
                + "UnknownHostException Exception " + se.getMessage());
            state = OutMessagingConstants.PING_FAILED;
        } catch (SocketException se) {
            logging.error(logPreString + "Socket Exception "
                + se.getMessage());
            state = OutMessagingConstants.PING_FAILED;
        } catch (IOException se) {
            logging.error(logPreString + "IOException Exception"
                + se.getMessage());
            state = OutMessagingConstants.PING_FAILED;
        }

        return state;
    }

    /**
     * The following method shuts down an ExecutorService in two phases, first
     * by calling shutdown to reject incoming tasks, and then calling
     * shutdownNow, if necessary, to cancel any lingering tasks (after 6
     * minutes).
     *
     * @param pool the executor service pool
     */
    private void shutdownAndAwaitTermination(final ExecutorService pool) {
        String logPreString = this.logPreString + "shutdownAndAwaitTermination() | -1 | ";
        logging.info(logPreString
            + "Executor pool waiting for tasks to complete");
        pool.shutdown(); // Disable new tasks from being submitted

        try {
            // Wait a while for existing tasks to terminate
            if (!pool.awaitTermination(6, TimeUnit.MINUTES)) {
                logging.error(logPreString
                    + "Executor pool  terminated with tasks "
                    + "unfinished. Unfinished tasks will be retried.");
                pool.shutdownNow(); // Cancel currently executing tasks

                // Wait a while for tasks to respond to being cancelled
                if (!pool.awaitTermination(6, TimeUnit.MINUTES)) {
                    logging.error(logPreString
                        + "Executor pool terminated with tasks "
                        + "unfinished. Unfinished tasks will be retried.");
                }
            } else {
                logging.info("Executor pool completed all tasks and has shut "
                    + "down normally");
            }
        } catch (InterruptedException ie) {
            logging.error(logPreString
                + "Executor pool shutdown error: " + ie.getMessage());
            // (Re-)Cancel if current thread also interrupted
            pool.shutdownNow();

            // Preserve interrupt status
            Thread.currentThread().interrupt();
        }

        isCurrentPoolShutDown = true;
    }

    /**
     * Gets whether the current pool has been shut down.
     *
     * @return whether the current pool has been shut down
     */
    public boolean getIsCurrentPoolShutDown() {
        return isCurrentPoolShutDown;
    }
}
