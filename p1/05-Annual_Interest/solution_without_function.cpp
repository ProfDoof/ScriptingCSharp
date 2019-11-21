#include <iostream>
using namespace std;

int main()
{
    double bal,rate;
    cout << "This program calculates the ending balance on a two-year savings account.\n\n";

    cout << "Starting balance? ";
    cin >> bal;
    cout << "Interest rate? ";
    cin >> rate;
    
    cout << "\n";
    bal *= (1+rate/100);
    cout << "Balance after one year: $" << bal << "\n";
    bal *= (1+rate/100);
    cout << "Balance after two years: $" << bal << "\n";
}

