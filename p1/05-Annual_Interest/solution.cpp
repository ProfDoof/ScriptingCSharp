#include <iostream>
#include <iomanip>
using namespace std;

float update_balance(float balance, float rate)
{
	return balance + ( balance * rate * .01 );
}

int main()
{
	float balance, rate;
	
	cout << "Starting balance? "; cin >> balance;
	cout << "Interest rate? "; cin >> rate;
	cout << endl;
	
	float one = update_balance(balance,rate);
	float two = update_balance(one,rate);
	float three = update_balance(two,rate);
	
	cout << fixed << setprecision(2);
	cout << "Balance after one year: $" << one << endl;
	cout << "Balance after two years: $" << two << endl;
	cout << "Balance after three years: $" << three << endl;
}
