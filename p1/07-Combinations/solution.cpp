#include <iostream>
using namespace std;

int Combinations(int n, int k)
{
    // Base cases - choose zero or all
    if( k == 0 || k == n )
        return 1;
    
    return Combinations(n-1,k-1) + Combinations(n-1,k);
}

int main()
{
    int n, k;
    cout << "Enter an Integer: ";
    cin >> n;
    cout << "Enter another Integer: ";
    cin >> k;
    
    int output = Combinations(n,k);
    // cout << "Factorial(" << n << ") = " << Factorial(n) << endl;
    // cout << "Factorial(" << k << ") = " << Factorial(k) << endl; 
    
    cout << "Combinations(" << n << "," << k << ") = ";
    cout << output << endl;
}

