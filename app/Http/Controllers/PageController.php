<?php

namespace App\Http\Controllers;

use App\Enums\BookStatus;
use App\Http\Requests\ContactRequest;
use App\Mail\ContactMessage;
use App\Models\Book;
use App\Models\Category;
use App\Support\RecentlyViewedBooks;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Category name → Material Symbols icon, for the homepage bento grid.
     * Falls back to a generic icon for any category not in this list, so
     * admin-created categories never break the page.
     *
     * @var array<string, string>
     */
    protected const CATEGORY_ICONS = [
        'الفقه الإسلامي' => 'gavel',
        'الحديث الشريف' => 'menu_book',
        'التفسير وعلوم القرآن' => 'auto_stories',
        'العقيدة' => 'star',
        'اللغة العربية' => 'translate',
        'التاريخ الإسلامي' => 'history_edu',
        'السيرة النبوية' => 'auto_stories',
        'الأدب' => 'translate',
        'أصول الفقه' => 'gavel',
        'الفتاوى' => 'gavel',
    ];

    public function home(): View
    {
        $popularBooks = Book::query()
            ->where('status', BookStatus::Available)
            ->with('author')
            ->orderByDesc('views_count')
            ->take(4)
            ->get();

        $recentlySearched = Book::query()
            ->whereIn('id', RecentlyViewedBooks::ids())
            ->with('author')
            ->get()
            ->sortBy(fn (Book $book) => array_search($book->id, RecentlyViewedBooks::ids()))
            ->values();

        $categories = Category::query()
            ->withCount('books')
            ->orderBy('name')
            ->take(6)
            ->get()
            ->map(fn (Category $category) => [
                'category' => $category,
                'icon' => self::CATEGORY_ICONS[$category->name] ?? 'category',
            ]);

        return view('welcome', [
            'popularBooks' => $popularBooks,
            'recentlySearched' => $recentlySearched,
            'categories' => $categories,
        ]);
    }

    public function about(): View
    {
        return view('site.about');
    }

    public function activities(): View
    {
        return view('site.activities');
    }

    public function donations(): View
    {
        return view('site.donations');
    }

    public function contact(): View
    {
        return view('site.contact');
    }

    public function contactStore(ContactRequest $request): RedirectResponse
    {
        $recipient = config('mail.contact_recipient');

        if ($recipient) {
            Mail::to($recipient)->send(new ContactMessage(
                senderName: $request->string('name')->toString(),
                senderEmail: $request->string('email')->toString(),
                messageBody: $request->string('message')->toString(),
            ));
        }

        return back()->with('status', __('تم إرسال رسالتك بنجاح، سنتواصل معكم قريباً بإذن الله.'));
    }
}
