/* Time.cpp
 *	This program accepts an integer number of seconds,
 *	and converts this value to hours, minutes, and seconds
 */

#include <iostream> 
using namespace std;

int main()
{
	cout << "This program converts seconds into hours, minutes and seconds." << endl;
	cout << "Enter the number of seconds: ";
	int total; cin >> total;
	
	int seconds = total % 60;
	int minutes = ( total / 60 ) % 60;
	int hours = total / 3600;
	
	cout << "This corresponds to " << hours << " hours, " << minutes << " minutes, and " << seconds << " seconds." << endl;
}
