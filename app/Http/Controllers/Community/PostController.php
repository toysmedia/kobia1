<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Community\CommunityCategory;
use App\Models\Community\CommunityPost;
use App\Models\Community\CommunityTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = CommunityPost::with(['user', 'category', 'tags'])->approved();

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }
        if ($request->filled('tag')) {
            $query->byTag($request->tag);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) => $q->where('title', 'LIKE', "%{$search}%")->orWhere('body', 'LIKE', "%{$search}%"));
        }

        $sort = $request->get('sort', 'latest');
        match($sort) {
            'popular'    => $query->popular(),
            'unanswered' => $query->where('replies_count', 0),
            default      => $query->latest(),
        };

        $posts = $query->paginate(15)->withQueryString();
        $categories = CommunityCategory::where('is_active', true)->get();
        $tags = CommunityTag::orderByDesc('usage_count')->limit(30)->get();

        return view('community.posts.index', compact('posts', 'categories', 'tags'));
    }

    public function show(Request $request, string $slug)
    {
        $post = CommunityPost::with(['user', 'category', 'tags'])->approved()->where('slug', $slug)->firstOrFail();

        $sessionKey = 'viewed_post_' . $post->id;
        if (!$request->session()->has($sessionKey)) {
            $post->increment('views_count');
            $request->session()->put($sessionKey, true);
        }

        $replies = $post->replies()
            ->with(['user', 'children.user', 'likes'])
            ->where('status', 'visible')
            ->latest()
            ->get();

        $userLiked = Auth::guard('community')->check()
            ? $post->likes()->where('community_user_id', Auth::guard('community')->id())->exists()
            : false;

        return view('community.posts.show', compact('post', 'replies', 'userLiked'));
    }

    public function create()
    {
        $categories = CommunityCategory::where('is_active', true)->orderBy('order')->get();
        $tags = CommunityTag::orderByDesc('usage_count')->limit(50)->get();
        return view('community.posts.create', compact('categories', 'tags'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => ['required', 'string', 'min:10', 'max:200'],
            'body'        => ['required', 'string', 'min:20'],
            'category_id' => ['required', 'exists:mysql.community_categories,id'],
            'type'        => ['required', 'in:question,discussion,article'],
            'tags'        => ['nullable', 'array', 'max:5'],
            'tags.*'      => ['string', 'max:50'],
        ]);

        $slug = Str::slug($request->title);
        $originalSlug = $slug;
        $count = 1;
        while (CommunityPost::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $post = CommunityPost::create([
            'community_user_id' => Auth::guard('community')->id(),
            'category_id'       => $request->category_id,
            'title'             => $request->title,
            'slug'              => $slug,
            'body'              => $request->body,
            'type'              => $request->type,
            'status'            => 'approved',
        ]);

        if ($request->filled('tags')) {
            $tagIds = [];
            foreach ($request->tags as $tagName) {
                $tagSlug = Str::slug($tagName);
                $tag = CommunityTag::firstOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
                $tag->increment('usage_count');
                $tagIds[] = $tag->id;
            }
            $post->tags()->sync($tagIds);
        }

        Auth::guard('community')->user()->addReputation(5);

        return redirect()->route('community.posts.show', $post->slug)
            ->with('success', 'Post created successfully!');
    }

    public function edit(string $slug)
    {
        $post = CommunityPost::where('slug', $slug)
            ->where('community_user_id', Auth::guard('community')->id())
            ->firstOrFail();

        $categories = CommunityCategory::where('is_active', true)->orderBy('order')->get();
        $tags = CommunityTag::orderByDesc('usage_count')->limit(50)->get();
        return view('community.posts.edit', compact('post', 'categories', 'tags'));
    }

    public function update(Request $request, string $slug)
    {
        $post = CommunityPost::where('slug', $slug)
            ->where('community_user_id', Auth::guard('community')->id())
            ->firstOrFail();

        $request->validate([
            'title'       => ['required', 'string', 'min:10', 'max:200'],
            'body'        => ['required', 'string', 'min:20'],
            'category_id' => ['required', 'exists:mysql.community_categories,id'],
            'tags'        => ['nullable', 'array', 'max:5'],
            'tags.*'      => ['string', 'max:50'],
        ]);

        $post->update([
            'title'       => $request->title,
            'body'        => $request->body,
            'category_id' => $request->category_id,
        ]);

        if ($request->has('tags')) {
            $post->tags()->each(fn($t) => $t->decrement('usage_count'));
            $tagIds = [];
            foreach ($request->tags as $tagName) {
                $tagSlug = Str::slug($tagName);
                $tag = CommunityTag::firstOrCreate(['slug' => $tagSlug], ['name' => $tagName]);
                $tag->increment('usage_count');
                $tagIds[] = $tag->id;
            }
            $post->tags()->sync($tagIds);
        }

        return redirect()->route('community.posts.show', $post->slug)->with('success', 'Post updated.');
    }

    public function destroy(string $slug)
    {
        $post = CommunityPost::where('slug', $slug)
            ->where('community_user_id', Auth::guard('community')->id())
            ->firstOrFail();

        $post->tags()->each(fn($t) => $t->decrement('usage_count'));
        $post->delete();

        return redirect()->route('community.posts.index')->with('success', 'Post deleted.');
    }
}
