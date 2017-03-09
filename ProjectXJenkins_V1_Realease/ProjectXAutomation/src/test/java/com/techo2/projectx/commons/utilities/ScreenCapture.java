package com.techo2.projectx.commons.utilities;

import static org.monte.media.FormatKeys.EncodingKey;
import static org.monte.media.FormatKeys.FrameRateKey;
import static org.monte.media.FormatKeys.KeyFrameIntervalKey;
import static org.monte.media.FormatKeys.MIME_AVI;
import static org.monte.media.FormatKeys.MediaTypeKey;
import static org.monte.media.FormatKeys.MimeTypeKey;
import static org.monte.media.VideoFormatKeys.CompressorNameKey;
import static org.monte.media.VideoFormatKeys.DepthKey;
import static org.monte.media.VideoFormatKeys.ENCODING_AVI_TECHSMITH_SCREEN_CAPTURE;
import static org.monte.media.VideoFormatKeys.QualityKey;

import java.awt.AWTException;
import java.awt.GraphicsConfiguration;
import java.awt.GraphicsDevice;
import java.awt.GraphicsEnvironment;
import java.awt.HeadlessException;
import java.io.File;
import java.io.IOException;
import java.nio.file.Files;
import java.util.Arrays;
import java.util.Comparator;

import org.apache.log4j.Logger;
import org.monte.media.Format;
import org.monte.media.FormatKeys.MediaType;
import org.monte.media.math.Rational;
import org.monte.screenrecorder.ScreenRecorder;
import org.openqa.selenium.OutputType;
import org.openqa.selenium.TakesScreenshot;
import org.openqa.selenium.WebDriver;

import com.techo2.projectx.commons.datahandlers.ConfigManager;

public class ScreenCapture  
{
	private static ScreenRecorder screenRecorder;
	private static String fileSeperator = System.getProperty("file.separator");
	private static Logger log =Logger.getLogger("ScreenCapture");
	public ScreenCapture()
	{
		
	}

	/**
	 * Method to start the video capturing using monte screen recorder.
	 */
	public static void startVideoCapture()
	{
		try
		{
			if(new ConfigManager().getProperty("VideoCapture").equalsIgnoreCase("true") && new ConfigManager().getProperty("ModeOfExecution").equalsIgnoreCase("linear"))
			{
				GraphicsDevice gd = GraphicsEnvironment.getLocalGraphicsEnvironment().getDefaultScreenDevice();
				int width = gd.getDisplayMode().getWidth();
				int height = gd.getDisplayMode().getHeight();
				java.awt.Rectangle captureArea = new java.awt.Rectangle(width,height);
				GraphicsConfiguration gc = GraphicsEnvironment
		            .getLocalGraphicsEnvironment()
		            .getDefaultScreenDevice()
		            .getDefaultConfiguration();
				File mediaFolder = new File(ReportSetup.getVideosPath());      
				screenRecorder = new ScreenRecorder(gc,captureArea,
				       new Format(MediaTypeKey, MediaType.FILE, MimeTypeKey, MIME_AVI),
				       new Format(MediaTypeKey, MediaType.VIDEO, EncodingKey, ENCODING_AVI_TECHSMITH_SCREEN_CAPTURE,
				            CompressorNameKey, ENCODING_AVI_TECHSMITH_SCREEN_CAPTURE,
				            DepthKey, 24, FrameRateKey, Rational.valueOf(15),
				            QualityKey, 1.0f,
				            KeyFrameIntervalKey, 15 * 60),
				       new Format(MediaTypeKey, MediaType.VIDEO, EncodingKey, "black",
				            FrameRateKey, Rational.valueOf(30)),
				      null,mediaFolder);
	
				screenRecorder.start();
			}
		}
		catch(HeadlessException he)
		{
			log.error("Display or mouse or keyboard might not be present..please ensure all devices are attached and working as expected..."+he.getStackTrace());
		}
		catch(IOException | AWTException e)
		{
			log.error("An Exception occured while recording screen..."+e.getCause());
		} 	
	}		


	/**
	 * Method to stop the video recording.
	 */
	public static void stopVideoCapture(String sVideoFileName)
	{
		try
		{
			if(new ConfigManager().getProperty("VideoCapture").equalsIgnoreCase("true") && new ConfigManager().getProperty("ModeOfExecution").equalsIgnoreCase("linear"))
			{
				screenRecorder.stop();
				renameCapturedVideoFile(sVideoFileName);
			}
		}
	
		catch(IOException e)
		{
			log.error("An Exception occured while recording screen..."+e.getCause());
		}
	}

	/**
	 * 
	 * Method to rename the captured video with respective test case name.
	 *
	 */
	
	public static void renameCapturedVideoFile(String sVideoFileName)
	{
		try
		{
			File mediaFolder=new File(ReportSetup.getVideosPath());
			File[] files = mediaFolder.listFiles();
		    Arrays.sort(files, new Comparator<Object>()
		    {
		    public int compare(Object o1, Object o2) 
		    {
		    	//return new Long(((File)o1).lastModified()).compareTo(new Long(((File)o2).lastModified())); // for ascending order
		    	return -1*(new Long(((File)o1).lastModified()).compareTo(new Long(((File)o2).lastModified()))); //for descending order 
		    }
		    });
		    File oldfile = new File(ReportSetup.getVideosPath()+fileSeperator+files[0].getName());
		    File newfile = null;
		    if(!new File(ReportSetup.getVideosPath()+fileSeperator+sVideoFileName+".avi").exists())
		    {
		    	newfile = new File(ReportSetup.getVideosPath()+fileSeperator+sVideoFileName+".avi");
		    }
		    else
		    {
		    	newfile = new File(ReportSetup.getVideosPath()+fileSeperator+sVideoFileName+"(1).avi");
		    }
		    boolean isFileRenamed = oldfile.renameTo(newfile);
		    if(!isFileRenamed)
		    {
		    	log.error("check folder permissions of project folder..\n cannot rename a screen recording..");
		    }
		}
		catch(Exception e)
		{
			log.error("An exception occured while renaming captured video files"+e.getCause());
		}	
	}
   
	
	/**
	 * Purpose - This method takes the screenshot and returns the Screenshot path
	 * @param driver
	 * @return - Returns screen shot name and location
	 */
	public static String saveScreenShot(WebDriver driver)
	{
		try
		{
			log.info("Saving screenshot of current browser window");
	        File screenshotFile = ((TakesScreenshot)driver).getScreenshotAs(OutputType.FILE);
	        String screenShotName = "Screenshot"+ UtilityMethods.getRandomNumber()+ ".jpeg";
			File targetFile = new File(ReportSetup.getImagesPath(), screenShotName);
			//FileUtils.copyFile(screenshotFile,targetFile );
			//String path =  targetFile.getAbsolutePath();
			Files.copy(screenshotFile.toPath(), targetFile.toPath());
	    	log.info("Screenshot created and : "+ screenShotName);
			return screenShotName;		
	    }
	    catch(Exception e)
	    {
	        log.error("An exception occured while saving screenshot of current browser window.."+e.getCause());
	        return null;
	    }
	}


}

