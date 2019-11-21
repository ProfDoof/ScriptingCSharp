#include <iostream>
#include <iomanip>
using namespace std;

int main()
{
	int qu, di, ni, pe;
	
	// Enter number of each type of coin
	cout << "Quarters: "; cin >> qu;
	cout << "Dimes: "; cin >> di;
	cout << "Nickels: "; cin >> ni;
	cout << "Pennies: "; cin >> pe;
	cout << endl;
	
	// Calculate and display total worth
    cout << fixed;
	cout << "The total is $" << setprecision(2) << qu*.25+di*.1+ni*.05+pe*.01 << endl;
}