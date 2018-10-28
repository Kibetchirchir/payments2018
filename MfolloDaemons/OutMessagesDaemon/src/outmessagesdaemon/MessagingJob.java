package outmessagesdaemon;

import outmessagesdaemon.db.MySQL;
import outmessagesdaemon.utils.Logging;
import outmessagesdaemon.utils.Props;
import outmessagesdaemon.utils.Utilities;
import outmessagesdaemon.utils.OutMessagingConstants;
import java.io.BufferedReader;
import java.io.DataOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.UnsupportedEncodingException;
import java.net.HttpURLConnection;
import java.net.URL;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.util.ArrayList;
import java.util.List;
import java.util.regex.Matcher;
import java.util.regex.Pattern;
import org.apache.http.client.ClientProtocolException;

@SuppressWarnings("FinalClass")
public final class MessagingJob implements Runnable {

    /**
     * The MySQL connection object.
     */
    private final MySQL mysql;
    /**
     * Logger for this class.
     */
    private final Logging logging;
    /**
     * The properties instance.
     */
    private final Props props;
    /**
     * The source number.
     */
    private final String source;
    /**
     * The destination number.
     */
    private final String destination;
    /**
     * The message to send.
     */
    private final String message;
    /**
     * The the message ID in the out messages table.
     */
    private final int outboundID;
    /**
     * The Connector rule to send message with.
     */
    private final String sendSMSURL;
    /**
     * The network service ID.
     */
    private final String serviceID;
    /**
     * The number of sends of the message.
     */
    private final int numberOfSends;
    /**
     * The maximum number of sends of sends for the message.
     */
    private final String spID;
    /**
     * The string to append before the string being logged
     */
    private String logPreString;

    /**
     * Constructor.
     *
     * @param logging
     * @param props
     * @param mySQL
     * @param outboundID
     * @param message
     * @param source
     * @param destination
     * @param numberOfSends
     * @param spID
     * @param serviceID
     * @param sendSMSURL
     */
    public MessagingJob(final Logging logging, final Props props, MySQL mySQL,
            final int outboundID, final String message,
            final String source, final String destination,
            final int numberOfSends, final String spID, final String serviceID,
            final String sendSMSURL) {
        this.logging = logging;
        this.props = props;
        this.source = source;
        this.destination = destination;
        this.message = message;
        this.numberOfSends = numberOfSends;
        this.spID = spID;
        this.outboundID = outboundID;
        this.serviceID = serviceID;
        this.sendSMSURL = sendSMSURL;
        this.mysql = mySQL;
        this.logPreString = "MessagingJob | ";

       // this.processRequest();
    }

    /**
     * This is the method called when this task is run. It creates a client
     * request to the server, gets and processes the response.
     */
    @SuppressWarnings({"SleepWhileInLoop", "SleepWhileHoldingLock"})
    private void processRequest() {
        this.logPreString = this.logPreString + "processRequest() | "
                + outboundID + " | ";

        /**
         * The JSON Reply from Hub.
         */
        String jsonReply = "";
        /**
         * The status code to update the record.
         */
        int statusCode = 0;
        /**
         * Status Description to update for the record.
         */
        String statusDescription = "";

        /**
         * The status code from Hub.
         */
        int hubStatCode = 0;
        /**
         * The returned outMessageID or client_sms_id.
         */
        int returnedOutMessageID = 0;

        /**
         * Success status from Hub.
         */
        boolean success = false;
        /**
         * The hub ID given by the API.
         */
        String gatewayRefID = "0";

        String stringRequest = "SOURCE=" + this.source + "&DEST=" + this.destination + "&MESSAGE=" + this.message + "&SPID=" + this.spID + "&SERVICEID=" + this.serviceID + "&LINKID=0&SMSID=" + this.outboundID;
        URL url;
        HttpURLConnection connection = null;
        String response = "";
        try {
            url = new URL(this.sendSMSURL + "?" + stringRequest);
            connection = (HttpURLConnection) url.openConnection();
            connection.setRequestMethod("POST");
            connection.setRequestProperty("Content-Type",
                    "application/x-www-form-urlencoded");

            connection.setRequestProperty("Content-Length", ""
                    + Integer.toString(stringRequest.getBytes().length));
            connection.setRequestProperty("Content-Language", "en-US");

            connection.setUseCaches(false);
            connection.setDoInput(true);
            connection.setDoOutput(true);
            connection.setConnectTimeout(props.getConnectionTimeout());
            connection.setReadTimeout(props.getConnectionTimeout());//set the connection time

            //Send request
            DataOutputStream wr = new DataOutputStream(
                    connection.getOutputStream());
            wr.writeBytes(stringRequest);
            wr.flush();
            wr.close();

            //Get Response	
            InputStream is = connection.getInputStream();
            BufferedReader rd = new BufferedReader(new InputStreamReader(is));
            String line;
            while ((line = rd.readLine()) != null) {
                response += line;
            }

            logging.info(this.logPreString + "routeRequest() | Response gotten from send SMS API => " + response);
            int httpCode = connection.getResponseCode();
            rd.close();
            /**
             * parse the response
             */

            if (httpCode == 200) {
                //gatewayRefID = response;

                Matcher matcher = Pattern.compile("<ns1:result>(.*?)</ns1:result>").matcher(response);
                while (matcher.find()) {
                    gatewayRefID = matcher.group(1);
                }

                if ("0".equals(gatewayRefID)) {
                    statusCode = props.getRetryStatus();
                } else {
                    statusCode = props.getProcessedStatus();
                }
                logging.info(this.logPreString + "routeRequest() | Successully sent because resultant HTTP code is " + httpCode);

            } else {
                gatewayRefID = "0";
                statusCode = props.getRetryStatus();
                logging.info("routeRequest() --- http code is not 200 is " + httpCode);

            }

        } catch (ClientProtocolException ex) {
            logging.error(logPreString
                    + "An ClientProtocolException has been caught while "
                    + "invoking the HUB SMS API. Error Message: "
                    + ex.getMessage());
            gatewayRefID = "0";
            statusCode = props.getRetryStatus();
            statusDescription = "Error setting the client protocol";
            returnedOutMessageID = this.outboundID;
        } catch (UnsupportedEncodingException ex) {
            logging.error(logPreString
                    + "An UnsupportedEncodingException has been caught "
                    + "while invoking the HUB SMS API. Error Message: "
                    + ex.getMessage());
            gatewayRefID = "0";
            statusCode = props.getRetryStatus();
            statusDescription = "Error Encoding the message to send";
            returnedOutMessageID = this.outboundID;
        } catch (IOException ex) {
            logging.error(logPreString
                    + "An IOException has been caught while invoking the "
                    + "HUB SMS API. Error Message: " + ex.getMessage());
            gatewayRefID = "0";
            statusCode = props.getRetryStatus();
            statusDescription = "Error: IOException caught while processing.";
            returnedOutMessageID = this.outboundID;

        } catch (Exception ex) {
            logging.error(logPreString
                    + "A " + ex.getClass() + " has been caught while processing. "
                    + " Error Message: ");
            gatewayRefID = "0";
            statusCode = props.getRetryStatus();
            statusDescription = "A " + ex.getClass() + " has been caught.";
            returnedOutMessageID = this.outboundID;
        } finally {

        }

        this.updateTransaction(returnedOutMessageID,
                statusCode, statusDescription, gatewayRefID);
    }

    /**
     * Acknowledges the transaction.
     *
     * @param returnedOutmessageID The out message ID returned as client sms ID
     * @param statusCode status code to update to
     * @param statusDescription status description
     * @param hubRefID the hub reference ID
     *
     * @return true if successful, false otherwise
     */
    private void updateTransaction(final int returnedOutmessageID,
            int statusCode, String statusDescription,
            final String gatewayRefID) {

        logPreString = this.logPreString + "updateTransaction() | "
                + this.outboundID + " | ";
        String query = "";
        String time = "";
        String firstSend = "";
        PreparedStatement stmt = null;
        Connection conn = null;

        if (numberOfSends + 1 >= this.props.getMaxFailedQueryRetries()
                && statusCode == props.getRetryStatus()) {
            statusCode = props.getEscalatedStatus();
            statusDescription = "Transaction Escalated. Cause of failure: "
                    + statusDescription;

        }

        query = "UPDATE outbound SET status = ?, bucketID=?, "
                + " gatewayUniqueID = ?, "
                + " nextSend = DATE_ADD("
                + " NOW(), INTERVAL ? MINUTE), numberOfSends"
                + " = (numberOfSends + 1)"
                + " WHERE outboundID = ?";
        String[] params = {
            String.valueOf(statusCode),
            String.valueOf(props.getUnprocessedStatus()),
            String.valueOf(gatewayRefID),
            String.valueOf(props.getNextSendInterval()),
            String.valueOf(outboundID)
        };
        String trueQuery = Utilities.prepareSqlString(query, params, 0);
        logging.info(logPreString
                + "Update query: " + trueQuery);
        try {
            conn = mysql.getConnection();
            stmt = conn.prepareStatement(query);
            stmt.setInt(1, statusCode);
            stmt.setInt(2, props.getUnprocessedStatus());
            stmt.setString(3, gatewayRefID);
            stmt.setInt(4, props.getNextSendInterval());
            stmt.setInt(5, outboundID);

            logging.info(logPreString
                    + "Updating message with outMessageID: "
                    + outboundID + " to status " + statusCode);

            stmt.executeUpdate();

            logging.info(logPreString
                    + "Message processed with outMessageID: "
                    + outboundID);

            stmt.close();
            stmt = null;
            conn.close();
            conn = null;
        } catch (Exception ex) {

            logging.error(logPreString
                    + "An error occured while updating the Message with "
                    + "outMessage ID: " + returnedOutmessageID + ". Error: "
                    + ex.getMessage());

            String modifiedQuery = "UPDATE outbound SET status = ?, bucketID=?, "
                    + " gatewayUniqueID = ?, "
                    + " nextSend = DATE_ADD("
                    + " NOW(), INTERVAL ? MINUTE), numberOfSends"
                    + " = (numberOfSends + 1)"
                    + " WHERE outboundID = ?";

            String trueStoreQuery = Utilities.prepareSqlString(modifiedQuery,
                    params, 0);
            Utilities.updateFile(OutMessagingConstants.FAILED_QUERIES_FILE,
                    trueStoreQuery);
        } finally {
            if (stmt != null) {
                try {
                    stmt.close();
                    stmt = null;
                } catch (Exception ex) {
                    logging.error(logPreString
                            + "Failed to close Statement object: "
                            + ex.getMessage());
                }
            }

            if (conn != null) {
                try {
                    conn.close();
                    conn = null;
                } catch (Exception ex) {
                    logging.error(logPreString
                            + "Failed to close connection object: "
                            + ex.getMessage());
                }
            }
        }

    }

    /**
     * Runs the task.
     */
    @Override
    public void run() {

        this.processRequest();

    }
}
