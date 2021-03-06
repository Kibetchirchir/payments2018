package outmessagesdaemon.utils;

import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.util.ArrayList;
import java.util.Collections;
import java.util.List;
import java.util.Properties;

/**
 * Loads system properties from a file.
 *
 * @author <a href="brian.ngure@cellulant.com">Brian Ngure</a>
 */
@SuppressWarnings({"FinalClass", "ClassWithoutLogger"})
public final class Props {

    /**
     * A list of any errors that occurred while loading the properties.
     */
    private transient List<String> loadErrors;
    /**
     * Info log level. Default = INFO.
     */
    private transient String infoLogLevel = "INFO";
    /**
     * Error log level. Default = FATAL.
     */
    private transient String errorLogLevel = "ERROR";
    /**
     * Info log file name.
     */
    private transient String infoLogFile;
    /**
     * Error log file name.
     */
    private transient String errorLogFile;
    /**
     * No of threads that will be created in the thread pool to process
     * payments.
     */
    private transient int numOfChildren;
    /**
     * Hub API URL.
     */
    private transient String hubAPIUrl;
    /**
     * API connection timeout.
     */
    private transient int connectionTimeout;
    /**
     * API reply timeout.
     */
    private transient int replyTimeout;
    /**
     * Sleep time.
     */
    private transient int sleepTime;
    /**
     * The properties file.
     */
    private static final String propsFile = "conf/MessagingProperties.xml";
    /**
     * The sms credentials properties file.
     */
    private static final String smsCredentialsFile = "conf/smsCredentials.properties";
    /**
     * The initialization vector used in SMS Password encryption.
     */
    private String initialisationVector;
    /**
     * The key used in SMS Password encryption.
     */
    private String encryptionKey;
    /**
     * Unprocessed Status.
     */
    private transient int unprocessedStatus;
    /**
     * Escalated Status.
     */
    private transient int retryStatus;
    /**
     * Escalated Status.
     */
    private transient int escalatedStatus;
    /**
     * Success Status.
     */
    private transient int processedStatus;
    /**
     * Database connection pool name.
     */
    private String dbPoolName;
    /**
     * Database user name.
     */
    private String dbUserName;
    /**
     * Database password.
     */
    private String dbPassword;
    /**
     * Database host.
     */
    private String dbHost;
    /**
     * Database port.
     */
    private String dbPort;
    /**
     * Database name.
     */
    private String dbName;
    /**
     * Maximum number of times to retry executing the failed text Query.
     */
    private int maxFailedQueryRetries;
    /**
     * Number of seconds before a next send.
     */
    private int nextSendInterval;
    /**
     * Maximum possible value of the run id.
     */
    private int maxRunID;
    /**
     * The high priority connector rule.
     */
    private int highPriorityCR;
    /**
     * Low priority connector rule.
     */
    private int lowPriorityCR;
    /**
     * Size of the messages to be fetched at one go.
     */
    private int bucketSize;
    /**
     * SMS API invocation username.
     */
    private String smsApiUsername;
    /**
     * SMS API invocation password.
     */
    private String smsApiPassword;
    /**
     * The Application name of the beep Messaging Daemon
     */
    private transient String appName;

    /**
     * Class Constructor.
     */
    public Props() {
        loadErrors = new ArrayList<String>(0);
        loadProperties(propsFile);
    }

    /**
     * Load system properties.
     *
     * @param propsFile the system properties xml file
     */
    @SuppressWarnings("UseOfSystemOutOrSystemErr")
    private void loadProperties(final String propsFile) {
        FileInputStream propsStream = null;
        Properties props;

        try {
            props = new Properties();
            propsStream = new FileInputStream(propsFile);
            props.loadFromXML(propsStream);

            String error1 = "ERROR: %s is <= 0 or may not have been set";
            String error2 = "ERROR: %s may not have been set";


            infoLogLevel = props.getProperty("InfoLogLevel");
            if (getInfoLogLevel().isEmpty()) {
                getLoadErrors().add(String.format(error2, "InfoLogLevel"));
            }

            errorLogLevel = props.getProperty("ErrorLogLevel");
            if (getErrorLogLevel().isEmpty()) {
                getLoadErrors().add(String.format(error2, "ErrorLogLevel"));
            }

            infoLogFile = props.getProperty("InfoLogFile");
            if (getInfoLogFile().isEmpty()) {
                getLoadErrors().add(String.format(error2, "InfoLogFile"));
            }

            errorLogFile = props.getProperty("ErrorLogFile");
            if (getErrorLogFile().isEmpty()) {
                getLoadErrors().add(String.format(error2, "ErrorLogFile"));
            }

            dbPoolName = props.getProperty("DbPoolName");
            if (getDbPoolName().isEmpty()) {
                getLoadErrors().add(String.format(error2, "DbPoolName"));
            }

            dbUserName = props.getProperty("DbUserName");
            if (getDbUserName().isEmpty()) {
                getLoadErrors().add(String.format(error2, "DbUserName"));
            }

            dbPassword = props.getProperty("DbPassword");
            if (getDbPassword().isEmpty()) {
                getLoadErrors().add(String.format(error2, "DbPassword"));
            }

            dbHost = props.getProperty("DbHost");
            if (getDbHost().isEmpty()) {
                getLoadErrors().add(String.format(error2, "DbHost"));
            }

            dbPort = props.getProperty("DbPort");
            if (getDbPort().isEmpty()) {
                getLoadErrors().add(String.format(error2, "DbPort"));
            }

            dbName = props.getProperty("DbName");
            if (getDbName().isEmpty()) {
                getLoadErrors().add(String.format(error2, "DbName"));
            }

            hubAPIUrl = props.getProperty("HubApiUrl");
            if (getHubAPIUrl().isEmpty()) {
                getLoadErrors().add(String.format(error2, "HubApiUrl"));
            }

            smsApiUsername = props.getProperty("SmsApiUsername");
            if (getSmsApiUsername().isEmpty()) {
                getLoadErrors().add(String.format(error2, "SmsApiUsername"));
            }
            smsApiPassword = props.getProperty("SmsApiPassword");
            if (getSmsApiPassword().isEmpty()) {
                getLoadErrors().add(String.format(error2, "SmsApiPassword"));
            }

            appName = props.getProperty("ApplicationName");
            if (getAppName().isEmpty()) {
                getLoadErrors().add(String.format(error2, "ApplicationName"));
            }

             initialisationVector = props.getProperty("IntializationVector");
            if (initialisationVector.isEmpty()) {
                getLoadErrors().add(String.format(error2, "IntializationVector"));
            }

            encryptionKey = props.getProperty("EncryptionKey");
            if (encryptionKey.isEmpty()) {
                getLoadErrors().add(String.format(error2, "EncryptionKey"));
            }
            
            String noc = props.getProperty("NumberOfThreads");
            if (noc.isEmpty()) {
                getLoadErrors().add(String.format(error1, "NumberOfThreads"));
            } else {
                numOfChildren = Integer.parseInt(noc);
                if (numOfChildren <= 0) {
                    getLoadErrors().add(String.format(error1,
                        "NumberOfThreads"));
                }
            }

            String connTimeout = props.getProperty("ConnectionTimeout");
            if (connTimeout.isEmpty()) {
                getLoadErrors().add(String.format(error1,
                    "ConnectionTimeout"));
            } else {
                connectionTimeout = Integer.parseInt(connTimeout);
                if (connectionTimeout < 0) {
                    getLoadErrors().add(String.format(error1,
                        "ConnectionTimeout"));
                }
            }

            String replyTO = props.getProperty("ReplyTimeout");
            if (replyTO.isEmpty()) {
                getLoadErrors().add(String.format(error1,
                    "ReplyTimeout"));
            } else {
                replyTimeout = Integer.parseInt(replyTO);
                if (replyTimeout < 0) {
                    getLoadErrors().add(String.format(error1,
                        "ReplyTimeout"));
                }
            }

            String sleep = props.getProperty("SleepTime");
            if (sleep.isEmpty()) {
                getLoadErrors().add(String.format(error1,
                    "SleepTime"));
            } else {
                sleepTime = Integer.parseInt(sleep);
                if (sleepTime < 0) {
                    getLoadErrors().add(String.format(error1,
                        "SleepTime"));
                }
            }

            String unprocessed = props.getProperty("UnprocessedStatus");
            if (unprocessed.isEmpty()) {
                getLoadErrors().add(String.format(error1,
                    "UnprocessedStatus"));
            } else {
                unprocessedStatus = Integer.parseInt(unprocessed);
                if (unprocessedStatus < 0) {
                    getLoadErrors().add(String.format(error1,
                        "UnprocessedStatus"));
                }
            }
   String retry = props.getProperty("RetryStatus");
            if (retry.isEmpty()) {
                getLoadErrors().add(String.format(error1,
                    "RetryStatus"));
            } else {
                retryStatus = Integer.parseInt(unprocessed);
                if (retryStatus < 0) {
                    getLoadErrors().add(String.format(error1,
                        "RetryStatus"));
                }
            }
            String processed = props.getProperty("ProcessedStatus");
            if (processed.isEmpty()) {
                getLoadErrors().add(String.format(error1,
                    "ProcessedStatus"));
            } else {
                processedStatus = Integer.parseInt(processed);
                if (processedStatus < 0) {
                    getLoadErrors().add(String.format(error1,
                        "ProcessedStatus"));
                }
            }
            String escalated = props.getProperty("EscalatedStatus");
            if (escalated.isEmpty()) {
                getLoadErrors().add(String.format(error1,
                    "EscalatedStatus"));
            } else {
                escalatedStatus = Integer.parseInt(escalated);
                if (escalatedStatus < 0) {
                    getLoadErrors().add(String.format(error1,
                        "EscalatedStatus"));
                }
            }


            String nsi = props.getProperty("NextSendInterval");
            if (!nsi.isEmpty()) {
                nextSendInterval = Integer.parseInt(nsi);
                if (nextSendInterval <= 0) {
                    getLoadErrors().add(String.format(error1,
                        "NextSendInterval"));
                }
            } else {
                loadErrors.add(String.format(error1,
                    "NextSendInterval"));
            }


            String hpcr = props.getProperty("HighPriorityConnectorRule");
            if (!hpcr.isEmpty()) {
                highPriorityCR = Integer.parseInt(hpcr);
                if (highPriorityCR <= 0) {
                    getLoadErrors().add(String.format(error1,
                        "HighPriorityConnectorRule"));
                }
            } else {
                getLoadErrors().add(String.format(error1,
                    "HighPriorityConnectorRule"));
            }


            String lpcr = props.getProperty("LowPriorityConnectorRule");
            if (!lpcr.isEmpty()) {
                lowPriorityCR = Integer.parseInt(lpcr);
                if (lowPriorityCR <= 0) {
                    getLoadErrors().add(String.format(error1,
                        "LowPriorityConnectorRule"));
                }
            } else {
                getLoadErrors().add(String.format(error1,
                    "LowPriorityConnectorRule"));
            }

            String maxFQretiries = props.getProperty("MaximumFailedQueryRetries");
            if (!maxFQretiries.isEmpty()) {
                maxFailedQueryRetries = Integer.parseInt(maxFQretiries);
                if (maxFailedQueryRetries <= 0) {
                    getLoadErrors().add(String.format(error1,
                        "MaximumFailedQueryRetries"));
                }
            } else {
                getLoadErrors().add(String.format(error1,
                    "MaximumFailedQueryRetries"));
            }

            String bucket = props.getProperty("BucketSize");
            if (!bucket.isEmpty()) {
                bucketSize = Integer.parseInt(bucket);
                if (bucketSize <= 0) {
                    getLoadErrors().add(String.format(error1,
                        "BucketSize"));
                }
            } else {
                getLoadErrors().add(String.format(error1,
                    "BucketSize"));
            }
            propsStream.close();
        } catch (NumberFormatException ne) {
            System.err.println("Exiting. String value found, Integer is "
                + "required: " + ne.getMessage());

            try {
                propsStream.close();
            } catch (IOException ex) {
                System.err.println("Failed to close the properties file: "
                    + ex.getMessage());
            }
            System.exit(1);
        } catch (FileNotFoundException ne) {
            System.err.println("Exiting. Could not find the properties file: "
                + ne.getMessage());

            try {
                propsStream.close();
            } catch (IOException ex) {
                System.err.println("Failed to close the properties file: "
                    + ex.getMessage());
            }

            System.exit(1);
        } catch (IOException ioe) {
            System.err.println("Exiting. Failed to load system properties: "
                + ioe.getMessage());

            try {
                propsStream.close();
            } catch (IOException ex) {
                System.err.println("Failed to close the properties file");
            }

            System.exit(1);
        }
    }

    /**
     * Info log level. Default = INFO.
     *
     * @return the infoLogLevel
     */
    public String getInfoLogLevel() {
        return infoLogLevel;
    }

    /**
     * Error log level. Default = FATAL.
     *
     * @return the errorLogLevel
     */
    public String getErrorLogLevel() {
        return errorLogLevel;
    }

    /**
     * Info log file name.
     *
     * @return the infoLogFile
     */
    public String getInfoLogFile() {
        return infoLogFile;
    }

    /**
     * Error log file name.
     *
     * @return the errorLogFile
     */
    public String getErrorLogFile() {
        return errorLogFile;
    }

    /**
     * Gets the Beep API URL.
     *
     * @return the Beep API URL
     */
    public String getHubAPIUrl() {
        return hubAPIUrl;
    }

    /**
     * No of threads that will be created in the thread pool to process
     * payments.
     *
     * @return the numOfChildren
     */
    public int getNumOfChildren() {
        return numOfChildren;
    }

    /**
     * Gets the connection timeout.
     *
     * @return the connection timeout
     */
    public int getConnectionTimeout() {
        return connectionTimeout;
    }

    /**
     * Gets the reply timeout.
     *
     * @return the reply timeout
     */
    public int getReplyTimeout() {
        return replyTimeout;
    }

    /**
     * Get the status for processing failure status
     *
     * @return the failure status
     */
    public int getUnprocessedStatus() {
        return unprocessedStatus;
    }
    
    public int getRetryStatus() {
        return retryStatus;
    }
    /**
     * Gets the status for processing escalated status
     *
     * @return the escalated status
     */
    public int getEscalatedStatus() {
        return escalatedStatus;
    }

    /**
     * Gets the status for processing success status
     *
     * @return the success status
     */
    public int getProcessedStatus() {
        return processedStatus;
    }

    /**
     * Gets the bucket size for processing
     *
     * @return the failure status
     */
    public int getBucketSize() {
        return bucketSize;
    }

    /**
     * Gets the sleep time.
     *
     * @return the sleep time
     */
    public int getSleepTime() {
        return sleepTime;
    }

    /**
     * A list of any errors that occurred while loading the properties.
     *
     * @return the loadErrors
     */
    public List<String> getLoadErrors() {
        return Collections.unmodifiableList(loadErrors);
    }

    /**
     * Contains the name of the database pool.
     *
     * @return the name of the database pool
     */
    public String getDbPoolName() {
        return dbPoolName;
    }

    /**
     * Contains the name of the database user.
     *
     * @return the name of the database user
     */
    public String getDbUserName() {
        return dbUserName;
    }

    /**
     * Contains the name of the database password.
     *
     * @return the name of the database password
     */
    public String getDbPassword() {
        return dbPassword;
    }

    /**
     * Contains the name of the database host.
     *
     * @return the name of the database host
     */
    public String getDbHost() {
        return dbHost;
    }

    /**
     * Contains the name of the database port.
     *
     * @return the name of the database port
     */
    public String getDbPort() {
        return dbPort;
    }

    /**
     * Contains the name of the database.
     *
     * @return the name of the database
     */
    public String getDbName() {
        return dbName;
    }

    /**
     * Maximum number of times to retry sending a payment.
     *
     * @return the maxSendRetries
     */
    public int getMaxFailedQueryRetries() {
        return maxFailedQueryRetries;
    }

    /**
     * Maximum number of times to retry sending a payment.
     *
     * @return the maxSendRetries
     */
    public int getNextSendInterval() {
        return nextSendInterval;
    }

    /**
     * Maximum possible value of the run id.
     *
     * @return the maxRunID
     */
    public int getMaxRunID() {
        return maxRunID;
    }

    /**
     * Gets the high priority connector rule to send to SMS API
     *
     * @return high priority connector Rules
     */
    public int getHighPriorityCR() {
        return highPriorityCR;
    }

    /**
     * Gets the low priority connector rule to send to SMS API
     *
     * @return low priority connector Rules
     */
    public int getLowPriorityCR() {
        return lowPriorityCR;
    }

    /**
     * Gets the SMS Connection credentials file
     *
     * @return SMS Credentials file name and path
     */
    public String getSmsCredentialsFile() {
        return smsCredentialsFile;
    }

    /**
     * Get the Encryption initialization vector.
     *
     * @return the initialization vector.
     */
    public String getInitialisationVector() {
        return initialisationVector;
    }

    /**
     * Get the Encryption Key.
     *
     * @return the Encryption Key.
     */
    public String getEncryptionKey() {
        return encryptionKey;
    }

    /**
     * Gets the sms API username to invoke with.
     *
     * @return sms API username
     */
    public String getSmsApiUsername() {
        return smsApiUsername;
    }

    /**
     * Gets the sms API Encrypted password to invoke with.
     *
     * @return sms API password
     */
    public String getSmsApiPassword() {
        return smsApiPassword;
    }
    /**
     * Gets the sms messaging application name.
     *
     * @return sms messaging application name
     */
    public String getAppName() {
        return appName;
    }
}
