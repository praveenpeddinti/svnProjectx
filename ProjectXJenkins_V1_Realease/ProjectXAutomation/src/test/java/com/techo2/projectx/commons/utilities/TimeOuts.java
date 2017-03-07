package com.techo2.projectx.commons.utilities;

public interface TimeOuts 
{
	public static final int VERYSHORTWAIT = UtilityMethods.getWaitTime("VERYSHORTWAIT");
	public static final int SHORTWAIT = UtilityMethods.getWaitTime("SHORTWAIT");
	public static final int MEDIUMWAIT = UtilityMethods.getWaitTime("MEDIUMWAIT");
	public static final int LONGWAIT = UtilityMethods.getWaitTime("LONGWAIT");
	public static final int VERYLONGWAIT = UtilityMethods.getWaitTime("VERYLONGWAIT");
	public static final int IMPLICITWAIT = UtilityMethods.getWaitTime("IMPLICITWAIT");

}
