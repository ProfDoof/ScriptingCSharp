#include <iostream>
using namespace std;

int main()
{
	int month, year;
	
	cout << "Month: "; cin >> month;
	cout << "Year: "; cin >> year;
	
	int days = 31;
	if( month == 4 || month == 6 || month == 9 || month == 11 ) // Apr, June, Sept, Nov
		days = 30;
	else if( month == 2 )
		days = 28;
	
	cout << endl << month << "/" << year << " has " << days << " days." << endl;
}
