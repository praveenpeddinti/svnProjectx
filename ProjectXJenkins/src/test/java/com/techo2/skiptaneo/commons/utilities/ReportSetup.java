/*************************************** PURPOSE **********************************

 - This class contains all methods related to HTML reporting
*/
package com.techo2.skiptaneo.commons.utilities;

import java.io.File;
import java.io.FileWriter;
import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.nio.file.attribute.BasicFileAttributeView;
import java.nio.file.attribute.BasicFileAttributes;

import org.apache.log4j.Logger;

import com.techo2.skiptaneo.commons.testng.Assert;



public class ReportSetup 
{
    private static String fileSeperator = System.getProperty("file.separator");
    private static Logger log = Logger.getLogger("ReportSetup");
    private static boolean isDirCreated = true;
    private static String message;
    private static String sBuff;
    private static FileWriter fW;
	private static FileWriter fWsummary;
	private static String sPageLoadfilepath;
  
    /**
	* This method setup's reporting environment i.e., creating a root folder and destination folder for storing report information
	*/
	public static void createFolderStructure()
	{
		createReportsFolder();
		createLatestResultsFolder();
      	createMediaFolders(); 	
	}

	/**
    * This method creates 'Automation Reports' directory if it does not exist
    */
	public static void createReportsFolder()
	{
		File file = new File(getReportsPath());
      	if (!file.exists())
    	{
      		isDirCreated = file.mkdir(); // mkdir creates a directory
    	}
      	if(!isDirCreated)
      	{
      		message = "\n Exception occured while creating 'Automation Results' directory";
			log.error("Check folder permissions of Project Directory..."+message);
			Assert.fail("Check folder permissions of Project Directory..."+message);
		}
	}
	
	/**
    * This method creates 'Latest Reports' directory if it does not exist
    * if directory exists it renames to Results_on_<currentDataTime> folder name and creates 'Latest Reports' directory
    */	
	public static void createLatestResultsFolder()
	{
		try 
		{
			File latestResults = new File(getLatestResultsPath());
	      	if(latestResults.exists())
	      	{
	      		Path p = Paths.get(getLatestResultsPath());
			    BasicFileAttributes view;
				
				view = Files.getFileAttributeView(p, BasicFileAttributeView.class).readAttributes();
			    String fCreationTime = view.creationTime().toString();
			    String istTime = UtilityMethods.convertToISTTime(fCreationTime.split("\\.")[0].replace("T","-"));
	      		String oldFolder = getReportsPath() + fileSeperator + "Results_on_" + istTime.replace(":", "_at_");
	      		File oldResults = new File(oldFolder);
	      		latestResults.renameTo(oldResults);
	      		
	      	}
	      	isDirCreated = latestResults.mkdir();
	      	if(!isDirCreated)
	      	{
	      		message = "\n Exception occured while creating 'Latest Results' directory";
				log.error("Check folder permissions of Project Directory..."+message);
				Assert.fail("Check folder permissions of Project Directory..."+message);
			}
		} 
		catch (IOException e) 
		{
			log.error("Exception occured while creating 'Latest Results' directory or unable to rename current 'Latest Results' directory "+e.getCause());
			Assert.fail("Exception occured while creating 'Latest Results' directory or unable to rename current 'Latest Results' directory "+e.getCause());
		}
	}
	

	/**
    * This method creates 'videos, screenshots' directory if they does not exist
    */	
	public static void createMediaFolders()
	{
		File videosFolder = new File(getVideosPath());
		if(!videosFolder.exists())
		{
			isDirCreated = videosFolder.mkdir();
			
		}
      	if(!isDirCreated)
      	{
      		message = "\n Exception occured while creating 'Latest Results/videos' directory";
			log.error("Check folder permissions of Project Directory..."+message);
			Assert.fail("Check folder permissions of Project Directory..."+message);
		}
      	
		File imagesFolder = new File(getImagesPath());
		if(!imagesFolder.exists())
		{
			isDirCreated = imagesFolder.mkdir();
		} 
      	if(!isDirCreated)
      	{
      		message = "\n Exception occured while creating 'Latest Results/screenshots' directory";
			log.error("Check folder permissions of Project Directory..."+message);
			Assert.fail("Check folder permissions of Project Directory..."+message);
		}
	}
	
	
	/**
	 *@return - This method returns path to the folder where screen recordings are stored
	 */
	public static String getVideosPath()
	{	
		return getLatestResultsPath()+fileSeperator+"Videos";
	}

	/**
	 *@return - This method returns path to the folder where screenshots are stored
	 */
	public static String getImagesPath()
	{
		return getLatestResultsPath()+ fileSeperator + "Screenshots";
	}


	/**
	 *@return - This method returns path to the folder where latest results are stored 
	 */
	public static String getLatestResultsPath()
	{
		return getReportsPath() + fileSeperator + "LatestResults";
	}
	
	/**
	 *@return - This method returns the path to the root of reports folder
	 */
	public static String getReportsPath()
	{
		return System.getProperty("user.dir") + fileSeperator + "Automation Reports";	
	}
	
	/**
	 * This method used to write page load time taken while navigating from one page to another page to 'PageLoadTime_Summary.html' file.
	 * @param sSource - Need to pass the source page name
	 * @param sDestination - Need to pass the destination page name
	 * @param sPageLoadTime - Need to pass the page load time
	 */
	public static void Report_PageLoadTime(String sSource, String sDestination, long sPageLoadTime)
	{
		sPageLoadfilepath = getLatestResultsPath() + fileSeperator + "PageLoadTime_Summary.html";
		String sMessage = "Page load time taken while navigating from '"+sSource+"' to '"+sDestination+"'";
		File file = new File(sPageLoadfilepath);
		
		if(file.exists())
		{
			
			try
			{	
				fW = new FileWriter(file,true);
				Write(sMessage, sPageLoadTime);
				log.info(sMessage + " has been logged in report");
			}
			catch( Exception e)
			{
				System.out.println("exception ="+ e.getMessage());
				log.error(sMessage + " has not been logged in report");
			}
		}
		else
		{
			sBuff = "";
			try 
			{
    			fW = new FileWriter(file);
    			Write(sMessage, sPageLoadTime);
    			log.info(sMessage + " has been logged in report");
    		}
			catch( Exception e)
			{
				System.out.println("exception ="+ e.getMessage());
				log.error(sMessage + " has not been logged in report");
			}
		} 
	}
	
	/**
	 * This method is used to write passed message text to 'PageLoadTime_Summary.html' file
	 * @param sMessage - Need the pass the message
	 * @param sPageLoadTime - Need to pass the page load time
	 * @throws Exception
	 */
	
	private static void Write(String sMessage, long sPageLoadTime) throws Exception
	{		
	
		if(sBuff== "done")
		{
			fW.write("<tr><td align=center><b>"+ sMessage + "</b></td><td align=center ><b>"+ sPageLoadTime+"</b></td></tr>");
    		fW.close();
    		
    	}
    	else
    	{
    		fW.write("<html><style type=text/css>table {font-size: 100%;}p{color:brown;text-align:center;font-family:verdana;font-weight:bold;font-size=12px;}</style><body><center><font color=blue face=Verdana>");
    		fW.write("<body <style type=text/css></style><body><center><font color=blue face=Verdana size=4> <b> PAGE LOAD TIME </b><br/><br/>");            
            fW.write("<table align=center border=1 width=100%><col width=500/><col width=50/><tr><th bgcolor=gray>Page Navigation &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th><th bgcolor=gray>Page Load Time (in MilliSeconds)</th></tr></font>");
            fW.write("<tr><td align=center><b>"+ sMessage + "</b></td><td align=center ><b>"+ sPageLoadTime+"</b></td></tr>");
            fW.close();
            sBuff = "done";
    	}	
	}
	
	
	/**
	 * This method used to write page load time taken while navigating any URL to 'PageLoadTime_Summary.html' file.
	 * @param sURL - Need to pass the URL
	 * @param sPageLoadTime - Need to pass the page load time
	 */
	
	public static void Report_PageLoadTime(String sURL, long sPageLoadTime)
	{
		sPageLoadfilepath = getLatestResultsPath() + fileSeperator + "PageLoadTime_Summary.html";
		String sMessage = "Page load time taken for navigating to '"+sURL+"' url";
		
		File file = new File(sPageLoadfilepath);
		
		if(file.exists())
		{
			
			try
			{	
				fW = new FileWriter(file,true);
				Write(sMessage,sPageLoadTime);
				log.info(sMessage + " has been logged in report");
			}
			catch( Exception e)
			{
				System.out.println("exception ="+ e.getMessage());
				log.error(sMessage + " has not been logged in report");
			}
		}
		else
		{
			sBuff = "";
			try 
			{
    			fW = new FileWriter(file);
    			Write(sMessage,sPageLoadTime);
    			log.info(sMessage + " has been logged in report");
    		}
			catch( Exception e)
			{
				System.out.println("exception ="+ e.getMessage());
				log.error(sMessage + " has not been logged in report");
			}
		} 
	}
	
	
	
	/**
	 * Purpose - To Mail the test summary file after completion of reporting
	 * @throws -  
	 */
	private static void emailHtmlSummaryReport(){
		
	}


}
	


