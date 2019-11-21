#include <iostream> 
using namespace std;

int main()
{
	cout << "This program converts seconds into hours, minutes and seconds." << endl;
	cout << "Enter the number of seconds: ";
	int total; cin >> total;
	
	int hours = total / 3600;
	total = total - (3600 * hours);
	
	int minutes = total / 60;
	total = total - (60 * minutes);
	
	int seconds = total;
		
	cout << "This corresponds to " << hours << " hours, " << minutes << " minutes, and " << seconds << " seconds." << endl;
}
