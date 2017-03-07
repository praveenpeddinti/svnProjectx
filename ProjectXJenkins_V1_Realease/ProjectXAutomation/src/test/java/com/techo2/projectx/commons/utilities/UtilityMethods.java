/*************************************** PURPOSE **********************************

 - This class contains all Generic methods which are not related to specific application
*/


package com.techo2.projectx.commons.utilities;

import java.io.File;
import java.io.IOException;
import java.net.InetAddress;
import java.net.UnknownHostException;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.Date;
import java.util.Random;
import java.util.TimeZone;

import javax.swing.JOptionPane;

import org.apache.commons.io.FileUtils;
import org.apache.log4j.Logger;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.WebDriver;

import com.mifmif.common.regex.Generex;
import com.techo2.projectx.commons.datahandlers.ConfigManager;
import com.techo2.projectx.commons.testng.Assert;


public class UtilityMethods 
{

	static ConfigManager sys = new ConfigManager();
	static Logger log = Logger.getLogger("UtilityMethods");
	static Thread thread;

	/**
	 * Purpose - to get the system name
	 * @return - String (returns system name)
	 */
    public static String machineName()
    {
    	String sComputername = null;
    	try 
    	{
			sComputername=InetAddress.getLocalHost().getHostName();
		} 
    	catch (UnknownHostException e) 
    	{
			log.error("Unable to get the hostname..."+ e.getCause());
		}
		return sComputername;
    }
    
    /**
     * TODO To get the entire exception stack trace
     * 
     * @return returns the stack trace
     */
    public static String getStackTrace()
    {
		String trace = "";
		int i;
		StackTraceElement[] stackTrace = Thread.currentThread().getStackTrace();
		for(i=2;i<stackTrace.length;i++)
		{
			trace = trace+"\n"+stackTrace[i];
		}
		return trace;
    }

    

    /**
     * Purpose - to get current date and time
     * @return - String (returns date and time)
     */
    public static String getCurrentDateTime()
    {
    	Calendar currentDate = Calendar.getInstance();
        SimpleDateFormat formatter= new SimpleDateFormat("dd-MMM-yyyy:HH.mm.ss");
        String dateNow = formatter.format(currentDate.getTime());
        return dateNow;    	
    }
    
    /**
	 * Purpose - To convert given time in "yyyy-MM-dd-HH:mm:ss" to IST time
	 * @returns date in String format 
	 * @throws Exception
	 */
	
    public static String convertToISTTime(String origTime) 
	{

        SimpleDateFormat formatter = new SimpleDateFormat("yyyy-MM-dd-HH:mm:ss");
        TimeZone obj = TimeZone.getTimeZone("GMT");
        formatter.setTimeZone(obj);
        try 
        {
			Date date = formatter.parse(origTime);
			formatter = new SimpleDateFormat("dd-MMM-yyyy:HH.mm.ss");
			//System.out.println(date);
			return formatter.format(date);
		} catch (ParseException e) {
			log.error("Cannot parse given date .." + origTime);
			log.info("returning current date and time .." + origTime);
		}
		return getCurrentDateTime();
    }    

    /**
     * Purpose - to display message box
     * @param infoMessage
     * @param location
     */
    public static void infoBox(String infoMessage, String location)
    {
        JOptionPane.showMessageDialog(null, infoMessage, "InfoBox: " + location, JOptionPane.INFORMATION_MESSAGE);
    }

    
    /**
     * Purpose - to stop the execution
     * @param suspendRunImagePath
     * @throws Exception
     */
    public static void suspendRun(String suspendRunImagePath) throws Exception
    {
    	Assert.fail("TEST RUN HAS BEEN SUSPENDED");
    }
    
    

    /**
     * Purpose - to generate the random number which will be used while saving a screenshot
     * @return - returns a random number
     */
 	public static int getRandomNumber()
 	{
 		Random rand = new Random();
 		int numNoRange = rand.nextInt();
 		return numNoRange;
     }
 	
 	/**
 	 * TODO Typecasts the wait time values from String to integer
 	 *
 	 * @param WaitTime
 	 * @return returns value of wait time in integer
 	 * @throws Exception
 	 */
 	public static int getWaitTime(String WaitType)
 	{
 		int iSecondsToWait = 15;
 		String wait;
 		if(WaitType!=null&&!WaitType.equalsIgnoreCase(""))
 		{
 			wait = sys.getProperty(WaitType);
 		}
 		else
 		{
 			log.error("WaitType cannot be empty...defaulting to MEDIUM WAIT");
 			wait = sys.getProperty("WAIT.MEDIUM");
 		}
 		try
 		{
 			iSecondsToWait = Integer.parseInt(wait);
 		}
 		catch(NumberFormatException e)
 		{
 			log.error("Please check the config file. Wait values must be a number...defaulting to 15 seconds");
 		} 		
 		return iSecondsToWait;		
 	}
 	
 	



	/**
	 * Method: To get caller method and class names
	 */
 	
/*	public static void preserveMethodName()
	{
		Throwable t = new Throwable(); 
		StackTraceElement[] elements = t.getStackTrace();
		String callerClassName = elements[1].getClassName();
		String callerMethodName = elements[1].getMethodName(); 
		String sMethod = "\"" + callerMethodName + "\"" + " method from " + callerClassName + " class";
		ReportHelper.setsMethodName(sMethod);		
	}*/
	
	/**
	 * 
	 * TODO method to run balloon popup process/method in a separate thread.
	 *
	 */
	public static void currentRunningTestCaseBalloonPopUp(final String sTestName)
	{
		thread = new Thread() 
		{
			 public void run()
			 {
				BalloonPopUp.currentRunningTestCase(sTestName);
			 }			
		};
		thread.start();
	}
	
	/**
	 * 
	 * TODO method to verify if the balloon pop process/method is ended or not.
	 */
	public static void verifyPopUp()
	{
		try 
		{
			thread.join();
		} catch (InterruptedException e) {
			log.error("balloon popup thread Interrupted"+e.getStackTrace());
		} 
	}

	
	/**
	 * 
	 * TODO Gives the name of operating system your are currently working on
	 * 
	 * @return returns the OS name
	 */
	public static String getOSName()
	{
		return System.getProperty("os.name");
	
	}	
	
	/**
	 * 
	 * TODO Gives the seperator value according to Operation System
	 * 
	 * @return returns the seperator with respect to Operation System
	 */
	public static String getFileSeperator()
	{		
		return System.getProperty("file.separator");
	}

	public static String generateStringFromRegEx(String regEx)
	{
		String expReg="[1-9] [a-f]";
		Generex generex = new Generex(expReg);
		String randomStr = generex.random();
		return randomStr;
	}
	
	/**
	 * 
	 * This method is used to delete all files and folders from specified directory path
	 *
	 * @param sFolderPath , Need to pass the fodler.directory path
	 */
	public void deleteAllExistingFilesOrFoldersFromSpecifiedDirectory(String sFolderPath)
	{
		File folder = new File(sFolderPath);
		try
		{
			FileUtils.cleanDirectory(folder);
			log.info("All files/Folders from specified folder is deleted successfully:"+sFolderPath);
		} 
		catch (IOException exception)
		{
			log.error("Unable to delete files/Folders from specified folder:"+sFolderPath);
			Assert.fail("IO Exception occured while trying to delete files/Fodlers from specified folder, Please close the files if they are opened: "+exception.getMessage());
		}
		catch (Exception exception)
		{
			log.error("Unable to delete files/Folders from specified folder:"+sFolderPath);
			Assert.fail("Some Exception occured while trying to delete files/Fodlers from specified folder, Please close the files if they are opened: "+exception.getMessage());
		}
	}
	
	/**
	 * This method is used to generate alert stating that Captcha should be entered manually within 30 secs.
	 */
	public void waitForCaptcha(WebDriver driver,int timeInSeconds)
	{
		JavascriptExecutor js = (JavascriptExecutor) driver;
		js.executeScript("alert('Please enter captcha manually. Captcha should be entered within "+timeInSeconds+" secs');");

		try {
			Thread.sleep(timeInSeconds*1000);
		} catch (InterruptedException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}
	
}
