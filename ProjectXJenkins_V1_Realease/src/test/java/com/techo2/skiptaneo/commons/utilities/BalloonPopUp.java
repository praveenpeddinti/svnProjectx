package com.techo2.skiptaneo.commons.utilities;

import java.awt.AWTException;
import java.awt.Image;
import java.awt.MenuItem;
import java.awt.PopupMenu;
import java.awt.SystemTray;
import java.awt.TrayIcon;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.net.MalformedURLException;
import java.net.URL;

import javax.swing.ImageIcon;

import org.apache.log4j.Logger;

import com.techo2.skiptaneo.commons.datahandlers.ConfigManager;

public class BalloonPopUp
{
	static Logger log = Logger.getLogger("BalloonPopUp");
	static String fileSeperator = System.getProperty("file.seperator");
	
	/**
	 * 
	 * TODO method used to indicate the current running testcase in a balloon pop in system tray
	 * @param testCaseName - Need to pass the Test case name which is currently running
	 * @throws Exception
	 */
	public static void currentRunningTestCase(String testCaseName)
	{
		 
        if (!SystemTray.isSupported())
        {           
            return;
        } 
             
        Image icon = createIcon();
        if (icon == null)
        {            
            return;
        }        
        final TrayIcon trayIcon = new TrayIcon(icon); 
        final SystemTray tray = SystemTray.getSystemTray();
        PopupMenu popup = new PopupMenu();
        MenuItem exit = new MenuItem("Exit"); 
        exit.addActionListener(new ActionListener()
        {
            @Override
            public void actionPerformed(ActionEvent e)
            {               
                tray.remove(trayIcon);
                System.exit(0);
            }
        });
 
        popup.add(exit);         
        
        trayIcon.setToolTip("Tray icon demo");
        trayIcon.setPopupMenu(popup); 
        try
        {
            tray.add(trayIcon);
        } 
        catch (AWTException e)
        {
            log.error("Could not load tray icon !");
        }
        
        trayIcon.displayMessage("Current Running TestCase", "The "+testCaseName+" Test case is running now", 
        TrayIcon.MessageType.INFO);         
       
        try
        {
        	int Time = Integer.parseInt(new ConfigManager().getProperty("BalloonPoPUp"));
            Thread.sleep(Time);
        } 
        catch (Exception e)
        {
        	log.error("Exception from Thread method");
        }
        
        tray.remove(trayIcon);
	}
    
	/**
	 * 
	 * TODO Method to create a icon in system tray
	 *
	 * @param path - Image icon path need to be provided
	 * @param description - Description for the icon Should be provided
	 * @return
	 * @throws Exception 
	 */   
	public static Image createIcon()
    {        
        URL imageURL;
        Image image = null;
		try 
		{
			imageURL = new URL("file:\\"+getImagePath());
	        if (getImagePath() == null)
	        {
	            log.error(getImagePath() + " not found");
	        } 
	        else
	        {
	            image = (new ImageIcon(imageURL, "Tray Icon")).getImage();
	        }
		} 
		catch (MalformedURLException e) 
		{
			log.error("url of tray icon image is malformed.."+e.getCause());
			e.printStackTrace();
		}
		return image;
    } 
    
    /**
     * 
     * TODO Method to get the path of image icon for system tray
     *
     * @return- returns the path of image icon
     */
    public static String getImagePath()
    {
		String path = System.getProperty("user.dir")+fileSeperator+"Resources"+fileSeperator+"Automation.jpg";		
		return path;
    }
        
}
